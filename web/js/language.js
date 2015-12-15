var language_selectors = document.querySelectorAll("[data-language-selector]");
var language_selectors_length = language_selectors.length;

var language_selects = document.querySelectorAll("[data-language-select]");
var language_selects_length = language_selects.length;

(function init() {
  function changeLanguage(language) {
    $.ajax({
      type: "POST",
      url: language,
      data: {
        "url": window.location.href
      },
      dataType: "json",
      success: function(data) {
        if (data.url) {
          window.location.href = data.url;
        }
        else {
         window.location.reload();
        }
      }
    });
  }

  for (var i = 0; i < language_selectors_length; i++) {
    var selector = language_selectors[i];

    selector.onclick = function(event) {
      var url = this.href;

      changeLanguage(url);

      event.preventDefault();
      return false;
    }
  }

  for (var i = 0; i < language_selects_length; i++) {
    var select = language_selects[i];

    select.onchange = function(event) {
      var url = this.value;

      changeLanguage(url);

      event.preventDefault();
      return false;
    }
  }
})();
