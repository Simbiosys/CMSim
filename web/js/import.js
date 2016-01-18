(function() {
    var global = {
      "host": document.getElementById("host").value,
      "check_created": document.getElementById("check_created"),
      "check_refresh": document.getElementById("check_refresh"),
      "check_equal": document.getElementById("check_equal"),
      "btn_delete_elements": document.getElementById("btn_delete_elements")
    };

    ////////////////////////////////////////////////////////////////////////////
    //                              CHECK SELECTORS
    ////////////////////////////////////////////////////////////////////////////

    global.check_created.onchange = function(event) {
      process("created", this.checked);
    }

    global.check_refresh.onchange = function(event) {
      process("updated", this.checked);
    }

    global.check_equal.onchange = function(event) {
      process("equal", this.checked);
    }

    function process(status, checked) {
      var rows = document.querySelectorAll("[data-status='" + status + "']");
      var rows_length = rows.length;

      for (var i = 0; i < rows_length; i++) {
        var row = rows[i];

        row.style.display = checked ? 'table-row' : 'none';
      }
    }

    ////////////////////////////////////////////////////////////////////////////
    //                             DELETE BUTTON
    ////////////////////////////////////////////////////////////////////////////

    global.btn_delete_elements.onclick = function(event) {
      var to_delete = document.querySelectorAll("[data-delete]:checked");
      var to_delete_length = to_delete.length;

      var ids = [];

      for (var i = 0; i < to_delete_length; i++) {
        var item = to_delete[i];
        var id = item.getAttribute("data-delete");

        ids.push(id);
        item.disabled = true;

        var row = document.querySelector("[data-delete-row='" + id + "']");
        row.className = 'line-through';
      }

      $.ajax({
        method: "POST",
        url: global.host + "/manager/pois/import/delete",
        data: {
          "pois": ids
        }
      });
    }
})();
