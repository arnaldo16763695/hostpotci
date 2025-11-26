//variables
let $inputEmail = document.getElementById("email");
let $linkTranference = document.getElementById("transference");
let $mac = document.getElementById("mac");
let $plans = document.querySelectorAll('input[name="plan"]');

// exist ?
if ($inputEmail && $linkTranference) {
  let baseUrl = $linkTranference.href;
  let plan = document.querySelector('input[name="plan"]:checked')?.value;
  let mac = $mac.value;
  let finalUrl = "";
  let email = $inputEmail.value;

  $inputEmail.addEventListener("input", function () {
    // console.log("Escribiendo...", e.target.value);
    email = this.value;
    finalUrl = baseUrl + '?email=' + email + '&plan=' + plan + '&mac=' + mac;
    console.log(finalUrl);
    $linkTranference.setAttribute("href", finalUrl);
  });

  $plans.forEach((radio) => {
    radio.addEventListener("change", function () {
      plan = this.value;
      finalUrl = baseUrl + '?email=' + email + '&plan=' + plan + '&mac=' + mac;
      console.log(finalUrl);
      $linkTranference.setAttribute("href", finalUrl);
    });
  });
} else {
  console.log("no existen los campos");
}
