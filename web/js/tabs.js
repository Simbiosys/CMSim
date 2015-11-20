(function() {
  var data_tabs = document.querySelectorAll("[data-tab]");
  var data_tabs_length = data_tabs.length;

  var data_contents = document.querySelectorAll("[data-content]");
  var data_contents_length = data_contents.length;

  function view_content(tab) {
    for (var i = 0; i < data_contents_length; i++) {
      var content = data_contents[i];
      content.style.display = 'none';
    }

    if (!tab && data_contents_length > 0) {
      data_contents[0].style.display = 'block';
      return;
    }

    var content = document.querySelector("[data-content='" + tab + "']");

    if (content) {
      content.style.display = 'block';
    }
  }

  function select_tab(tab_name) {
    for (var i = 0; i < data_tabs_length; i++) {
      var tab = data_tabs[i];
      tab.parentNode.className = '';
    }

    if (!tab) {
      return;
    }

    var tab = document.querySelector("[data-tab='" + tab_name + "']");

    if (tab) {
      tab.parentNode.className = 'active';
    }
  }

  function handle_tabs() {
    for (var i = 0; i < data_tabs_length; i++) {
      var tab = data_tabs[i];

      tab.onclick = function(event) {
        var tab = this.getAttribute("data-tab");
        select_tab(tab);
        view_content(tab);

        event.preventDefault();

        return false;
      }
    }
  }

  (function init() {
    view_content();
    handle_tabs();
  })();
})();
