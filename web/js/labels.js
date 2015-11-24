(function() {
  var buttons = document.querySelectorAll("[data-label-add]");
  var buttons_length = buttons.length;

  for (var i = 0; i < buttons_length; i++) {
    var button = buttons[i];

    var selector = button.getAttribute("data-label-selector");
    selector = document.getElementById(selector);

    var container = button.getAttribute("data-label-add");
    container = document.getElementById(container);

    var model = container.getAttribute("data-model");
    var fk = container.getAttribute("data-fk");

    checkContainer(container, selector);

    button.onclick = function(evento) {
      evento.preventDefault();

      var selector = this.getAttribute("data-label-selector");
      selector = document.getElementById(selector);

      if (!selector.value) {
        return false;
      }

      var container = this.getAttribute("data-label-add");
      container = document.getElementById(container);

      var label = container.querySelector("[data-label='" + selector.value + "']");

      if (label) {
        return false;
      }

      var div = document.createElement("div");
      container.appendChild(div);
      div.className = "label";
      div.setAttribute("data-label", selector.value);
      div.setAttribute("data-selector", this.getAttribute("data-label-selector"));
      div.setAttribute("data-row", "");
      div.setAttribute("data-model", model);
      div.setAttribute("data-fk", fk);

      var id = document.getElementById("id");

      if (id) {
        id = id.value;
      }

      div.setAttribute("data-fk-value", id);
      div.setAttribute("data-field", "");
      div.setAttribute("data-name", "label_id");
      div.setAttribute("data-value", selector.value);

      var span = document.createElement("span");
      div.appendChild(span);
      span.innerHTML = selector.options[selector.selectedIndex].innerHTML;

      var a = document.createElement("a");
      div.appendChild(a);
      a.href = "javascript:void(0)";
      a.className = "vertical-align-middle";

      var i = document.createElement("i");
      a.appendChild(i);
      i.className = "fa fa-times fa-lg";
      a.onclick = removeLabel;

      selector.options[selector.selectedIndex].disabled = true;
      selector.selectedIndex = 0;

      return false;
    }
  }

  var removes = document.querySelectorAll("[data-remove-label]");
  var removes_length = removes.length;

  for (var i = 0; i < removes_length; i++) {
    var remove = removes[i];
    remove.onclick = removeLabel;

    var selector = remove.getAttribute("data-selector");

    if (!selector) {
      continue;
    }

    selector = document.getElementById(selector);

    if (!selector) {
      continue;
    }

    var id = remove.getAttribute("data-remove-label");
    var option = selector.querySelector("[value='" + id + "']");

    if (option) {
      option.disabled = true;
    }
  }

  function removeLabel() {
    var label = this.parentNode;
    var value = label.getAttribute("data-label");

    label.parentNode.removeChild(label);

    var selector = label.getAttribute("data-selector");
    selector = document.getElementById(selector);

    var option = selector.querySelector("[value='" + value + "']");

    if (option) {
      option.disabled = false;
    }
  }

  function checkContainer(container, selector) {
    var labels = container.querySelectorAll("[data-label]");
    var labels_length = labels.length;

    for (var i = 0; i < labels_length; i++) {
      var label = labels[i];
      var value = label.getAttribute("data-label");

      var option = container.querySelector("[value='" + value + "']");

      if (option) {
        option.disabled = true;
      }
    }
  }
})();
