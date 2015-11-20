var host = document.getElementById("host").value;
var admin_customer = document.getElementById("admin_customer");

if (admin_customer) {
  admin_customer.onchange = function() {
    $.ajax({
      type: "POST",
      url: host + "/impersonate/" + this.value,
      success: function() {
        window.location.reload();
      }
    });
  }
}
