//variables
const $inputEmail = document.getElementById("email");
const $linkTranference = document.getElementById("transference");
const $plans = document.querySelectorAll('input[name="plan"]');
const $macInput = document.getElementById("mac");
const $ipInput = document.getElementById("ip");
const $phoneInput = document.getElementById("phone");

// Check required elements
if ($inputEmail && $linkTranference && $plans.length > 0) {
  // Base URL (without query string)
  const baseUrl = $linkTranference.href.split("?")[0];

  // MAC is fixed (from hidden input)
  const mac = $macInput ? $macInput.value : "";
  const phone = $phoneInput ? $phoneInput.value : "";
  const ip = $ipInput ? $ipInput.value : "";

  function updateTransferLink() {
    const email = $inputEmail.value.trim();
    const myPhone = $phoneInput.value.trim();
    const selectedPlan =
      document.querySelector('input[name="plan"]:checked')?.value || "";

    // Build query string safely
    const params = new URLSearchParams();

    if (mac) params.append("mac", mac);
    if (ip) params.append("ip", ip);
    if (myPhone) params.append("phone", myPhone);
    if (selectedPlan) params.append("plan", selectedPlan);
    if (email) params.append("email", email);

    const finalUrl = params.toString()
      ? `${baseUrl}?${params.toString()}`
      : baseUrl;

    // Debug (optional)
    console.log("Transfer link:", finalUrl);

    $linkTranference.href = finalUrl;
  }

  // Events
  $inputEmail.addEventListener("input", updateTransferLink);
  $phoneInput.addEventListener("input", updateTransferLink);
  $plans.forEach((radio) => {
    radio.addEventListener("change", updateTransferLink);
  });

  // Initialize link on page load
  updateTransferLink();
} else {
  console.warn("Required elements do not exist on this page.");
}
