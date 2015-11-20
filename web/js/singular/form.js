(function() {
  var forms = document.querySelectorAll("[data-form]");
  var forms_length = forms.length;

  for (var i = 0; i < forms_length; i++) {
    var form = forms[i];

    form.onsubmit = function(event) {
      event.preventDefault();

      processFormData(form);
      form.send();

      return false;
    }
  }

  function processFormData(form) {
    // Process form's model
    processModelChildren(form);

    // Process other models
    var models = form.querySelectorAll("[data-model]");
    var models_length = models.length;

    for (var i = 0; i < models_length; i++) {
      var model = models[i];
      processModelChildren(model);
    }

    // Process rows
    var rows = form.querySelectorAll("[data-row]");
    var rows_length = rows.length;

    for (var i = 0; i < rows_length; i++) {
      var row = rows[i];
      processRowChildren(row);
    }

    // Send data
    form.send = sendForm;
  }

  function processModelChildren(container) {
    if (!container.hasAttribute("data-model")) {
      console.error("'data-model' attribute is missing");
      return;
    }

    var model = container.getAttribute("data-model");

    var fields = container.querySelectorAll("[data-field]");
    var fields_length = fields.length;

    for (var i = 0; i < fields_length; i++) {
      var field = fields[i];
      field.setAttribute("data-model", model);
    }
  }

  function processRowChildren(container) {
    if (!container.hasAttribute("data-row")) {
      return;
    }

    if (!container.hasAttribute("data-fk")) {
      console.error("A 'data-row' elements must contain a 'data-fk' attribute.");
      return;
    }

    if (!container.hasAttribute("data-fk-value")) {
      console.error("A 'data-row' elements must contain a 'data-fk-value' attribute.");
      return;
    }

    var id = guid();
    container.setAttribute("data-row", id);

    var fields = container.querySelectorAll("[data-field]");
    fields = Array.prototype.slice.call(fields, 0); // to array

    if (container.hasAttribute("data-field")) {
      fields.push(container);
    }

    var fields_length = fields.length;

    for (var i = 0; i < fields_length; i++) {
      var field = fields[i];
      field.setAttribute("data-belong-to-row", id);
    }
  }

  function guid() {
    function s4() {
      return Math.floor((1 + Math.random()) * 0x10000)
        .toString(16)
        .substring(1);
    }

    return "u" + s4() + s4() + '-' + s4() + '-' + s4() + '-' +
      s4() + '-' + s4() + s4() + s4();
  }

  function sendForm() {
    var form = this;

    var data = {};
    var rows = {};

    var fields = form.querySelectorAll("[data-field]");
    var fields_length = fields.length;

    for (var i = 0; i < fields_length; i++) {
      var field = fields[i];

      var name = field.hasAttribute("data-name") ?
          field.getAttribute("data-name") : field.getAttribute("name");

      var value = field.hasAttribute("data-value") ?
          field.getAttribute("data-value") : field.value;

      var model = field.getAttribute("data-model");

      if (field.hasAttribute("data-belong-to-row")) {
        var row_id = field.getAttribute("data-belong-to-row");

        if (!rows[model]) {
          rows[model] = {};
        }

        if (!rows[model][row_id]) {
          rows[model][row_id] = {};

          var row = form.querySelector("[data-row='" + row_id + "']");
          var fk = row.getAttribute("data-fk");
          var fk_value = row.getAttribute("data-fk-value");

          rows[model][row_id][fk] = fk_value;
        }

        rows[model][row_id][name] = value;
      }
      else {
        if (!data[model]) {
          data[model] = {};
        }

        data[model][name] = value;
      }
    }

    for (var model in rows) {
      if (!data[model]) {
        data[model] = [];
      }

      var model_data = rows[model];

      for (row in model_data) {
        var row_data = model_data[row];
        data[model].push(row_data);
      }
    }

    var method = form.hasAttribute("data-method") ?
      form.getAttribute("data-method") : form.getAttribute("method");

    var action = form.hasAttribute("data-action") ?
      form.getAttribute("data-action") : form.getAttribute("action");

    var ajax = form.hasAttribute("data-ajax");

    if (ajax) {
      $.ajax({
        "method": method,
        "url": action,
        "data": data
      })
      .done(function(data) {

      });
    }
    else {
      var inputs = {};

      for (var element in data) {
        var element_data = data[element];

        if (Object.prototype.toString.call(element_data) === '[object Array]') {
          var length = element_data.length;

          for (var i = 0; i < length; i++) {
            var obj = element_data[i];
            var name = element + "[" + i + "]";

            processField(name, obj, inputs);
          }
        }
        else if (typeof element_data === 'object') {
          processField(element, element_data, inputs);
        }
      }

      var form = document.createElement("form");
      form.action = action;
      form.method = method;

      for (var input in inputs) {
        var value = inputs[input];

        var obj = document.createElement("input");
        obj.type = "hidden";
        obj.name = input;
        obj.value = value;

        form.appendChild(obj);
      }

      form.submit();
    }
  }

  function processField(prefix, data, inputs) {
    for (var obj in data) {
      var value = data[obj];
      var name = prefix + "[" + obj + "]";

      inputs[name] = value;
    }
  }
})();
