var language_selectors = document.querySelectorAll("[data-language-selector]");
var language_selectors_length = language_selectors.length;

(function init() {
  for (var i = 0; i < language_selectors_length; i++) {
    var selector = language_selectors[i];

    selector.onclick = function(event) {
      var url = this.href;

      $.ajax({
        type: "POST",
        url: url,
        data: {
          "url": window.location.href
        },
        dataType: "json",
        success: function(data) { console.log(data)
          if (data.url) {
            window.location.href = data.url;
          }
          else {
           window.location.reload();
          }
        }
      });

      event.preventDefault();
      return false;
    }
  }
})();
