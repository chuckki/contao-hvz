$(document).ready(function() {

  function getParameterByName(name) {
      name = name.replace(/[\[]/, "\\[").replace(/[\]]/, "\\]");
      var regex = new RegExp("[\\?&]" + name + "=([^&#]*)"),
          results = regex.exec(location.search);
      return results === null ? "" : decodeURIComponent(results[1].replace(/\+/g, " "));
  }

  function CheckWindow(win){
    if (win.width() < 806) {
      if(!$(".searchicon").length){
        $(".logo").append('<div ontouchstart="return true;" class="searchicon fa fa-search fa-3x" ></div>');
        $(".searchicon").click(function() {
           scrollToAnchor('tags');
           $("#tags").focus();
          return false;
        });
      };
    }else{
      $(".searchicon").remove();
    }
  }

  if( getParameterByName('search') == 'true'){
    $('#tags').focus();
  }

  $(window).on('resize', function(){
        CheckWindow($(this));
  });

  CheckWindow($(window));

  $(".searchicon").click(function() {
     scrollToAnchor('tags');
     $("#tags").focus();
    return false;
  });

  var country = 'de';

  $('.custom-select').change(function() {
    country = this.value;
    $('.country-select-wrapper').removeClass('de');
    $('.country-select-wrapper').removeClass('at');
    $('.country-select-wrapper').addClass(country);
  });

  var myloc = new Bloodhound({
      datumTokenizer: Bloodhound.tokenizers.obj.whitespace('ort'),
      queryTokenizer: Bloodhound.tokenizers.whitespace,
      remote: {
        url: "/search/%QUERY",
        wildcard: '%QUERY',
        prepare: function(query, settings) {
            settings.url = '/search/' + query + '?c=' + country;
            return settings;
            },
      }
  });

  myloc.initialize();

  $('#tags').typeahead({
    minLength: 1
  },{
      source: myloc.ttAdapter(),
      name: 'myorts',
      displayKey: 'ort',
      limit:20
    });

  $('#tags').on('typeahead:selected', function(evt, item) {
    window.location.href= "/halteverbot-suche.html?suche=" + encodeURIComponent($("#tags").val());
  });

  $('#tags').keypress(function (e) {
    if (e.which == 13) {
      var myURI = $("#tags").val();
          window.location.href= "/halteverbot-suche.html?suche=" + encodeURIComponent(myURI);
    }
  });


  $('#tags').focusout(function(e) {
          if($(window).width() < 800) {
            $(".tt-suggestion:first-child").trigger('click');
          }
          return true;
  });

  $('.citysearch input').on('focus', function(evt, item) {
     scrollToAnchor('hvzlistdropdown');
  });

});
