## Install
check also rabatt-README !!
```PHP
# app/AppKernel
    new ContaoModuleBundle('hvz', $this->getRootDir()),
```


## Modulbeschreibung

- HvzList -> deutschlandweit übersicht
- HvzListDropDown -> Typeahead
- HvzReader -> Hvz Detailseite
- HvzResult -> Hvz Suche/Ergebnisse
- HvzTeaser -> Featured Orte mit Karte

## assets/js

| Skript | Modules  | via   |
|---|---|---|
| pikaday.min.js           | ModuleHvzReader        | TL_JAVASCRIPT |
| validateForm.min.js      | ModuleHvzReader        | TL_BODY       |
| searchlist.min.js        | ModuleHvzListDropDown  | TL_JAVASCRIPT |
| typeahead.bundle.min.js  | ModuleHvzListDropDown  | TL_JAVASCRIPT |

## Doings
- composer
- Rabatte via Bundle 
- Backend Icon via hvz-Bundle nicht Rabatt-Bundle
- rabatt mit datums validierung start-stop