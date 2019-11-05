<?php

/*
 * This file is part of backend-hvb.
 *
 * (c) Dennis Esken - callme@projektorientiert.de
 *
 * @license NO LICENSE - So dont use it without permission (it could be expensive..)
 */

namespace Chuckki\ContaoHvzBundle;

use Contao\CoreBundle\Framework\ContaoFrameworkInterface;
use Exception;
use Klarna\Rest\Payments\Orders;
use Klarna\Rest\Payments\Sessions;
use Klarna\Rest\Transport\Connector;
use Psr\Log\LoggerInterface;

/**
 * Class HvzKlarna.
 */
class HvzKlarna
{
    private $merchantId;
    private $sharedSecret;
    private $klarna_env;
    private $logger;
    private $contaoFramework;

    public function __construct(LoggerInterface $logger, ContaoFrameworkInterface $contaoFramework)
    {
        $this->contaoFramework = $contaoFramework;
        $this->contaoFramework->initialize();
        $this->initialCredits($GLOBALS['TL_CONFIG']);
        $this->logger = $logger;
    }

    public function initialCredits(array $conf)
    {
        $this->klarna_env = (!$conf['klarna_env']) ? 'https://api.klarna.com' : 'https://api.playground.klarna.com';
        $this->merchantId = $conf['klarna_user'];
        $this->sharedSecret = $conf['klarna_pw'];
    }

    public function getKlarnaNewOrderSession(HvzOrderModel $hvzOrderModel): Sessions
    {
        $order = self::getBillingData($hvzOrderModel);
        try {
            $session = $this->getKlarnaSession($hvzOrderModel);
            $session->create($order);

            return $session;
        } catch (Exception $e) {
            $this->logger->error('Not possible to create Klarnaorder', [$e->getMessage()]);
            PushMeMessage::pushMe('Not possible to create Klarnaorder', 'HvzKlarna');
            exit(0);
        }
        die('Klarna Payments got some errors');
    }

    public function getKlarnaSession(HvzOrderModel $hvzOrderModel, $sessionId = null): Sessions
    {
        $connector = Connector::create($this->merchantId, $this->sharedSecret, $this->klarna_env);
        try {
            $session = new Sessions($connector);

            return $session;
        } catch (Exception $e) {
            $this->logger->error('Not possible to get Klarnasession', [$e->getMessage()]);
            PushMeMessage::pushMe('Not possible to get Klarnasession','HvzKlarna');
            exit(0);
        }
        die('Klarna Payments got some errors');
    }

    public function executePayment(HvzOrderModel $orderModel)
    {
        $connector = Connector::create($this->merchantId, $this->sharedSecret, $this->klarna_env);
        $authorizationToken = $orderModel->klarna_auth_token ?: 'authorizationToken';
        try {
            $order = new Orders($connector, $authorizationToken);
            $data = $order->create(self::getBillingData($orderModel));

            return $data;
        } catch (Exception $e) {
            $this->logger->error('Not possible to execute Klarna Payment', [$e->getMessage()]);
            PushMeMessage::pushMe('Not possible to execute Klarna Payment','HvzKlarna');
            exit(0);
        }
        die('Klarna Payments got some errors');
    }

    private function getBillingData(HvzOrderModel $hvzOrderModel): array
    {
        return [
            'auto_capture' => true,
            //https://developers.klarna.com/documentation/klarna-payments/integration-guide/place-order#4-3-place-recurring-order-tokenization
            'purchase_country' => 'DE',
            'purchase_currency' => 'EUR',
            'locale' => 'de-DE',
            'merchant_data' => $hvzOrderModel->orderNumber,
            'merchant_reference1' => $hvzOrderModel->orderNumber,
            'order_amount' => self::getCents($hvzOrderModel->getBrutto()),
            'order_tax_amount' => self::getCents($hvzOrderModel->getMwSt()),
            'order_lines' => [
                [
                    'type' => 'physical',
                    'reference' => $hvzOrderModel->hvz,
                    'name' => 'Halterverbotszone in '.$hvzOrderModel->hvz_ort,
                    'quantity' => 1,
                    'product_url' => $hvzOrderModel->getAbsoluteUrl(),
                    'tax_rate' => (HvzOrderModel::MWST_INTL_GERMANY) * 100,
                    'total_tax_amount' => self::getCents($hvzOrderModel->getMwSt()),
                    'total_amount' => self::getCents($hvzOrderModel->getBrutto()),
                    'unit_price' => self::getCents(
                        $hvzOrderModel->getBrutto() + $hvzOrderModel->getRabatt()
                    ),
                    'total_discount_amount' => self::getCents($hvzOrderModel->getRabatt()),
                ],
            ],
            'merchant_urls' => [
                'confirmation' => $hvzOrderModel->getFinishOrderPage(),
                'notification' => $hvzOrderModel->getErrorOrderPage(), // optional
            ],
        ];
    }

    private function getCents(string $string)
    {
        $string = round((float) $string, 5);
        $arr = explode('.', $string);
        if (2 === \count($arr)) {
            if (1 === \strlen($arr[1])) {
                $arr[1] .= '0';
            }
            $value = (int) implode('', $arr);
        } elseif (1 === \count($arr)) {
            $value = (int) (implode('', $arr).'00');
        }

        return $value;
    }
}
