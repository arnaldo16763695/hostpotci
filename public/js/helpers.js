//variables
const $inputEmail      = document.getElementById('email');
const $linkTranference = document.getElementById('transference');
const $plans           = document.querySelectorAll('input[name="plan"]');
const $macInput        = document.getElementById('mac');
const $ipInput        = document.getElementById('ip');



// Check required elements
if ($inputEmail && $linkTranference && $plans.length > 0) {
  // Base URL (without query string)
  const baseUrl = $linkTranference.href.split('?')[0];

  // MAC is fixed (from hidden input)
  const mac = $macInput ? $macInput.value : '';
  const ip = $ipInput ? $ipInput.value : '';

  function updateTransferLink() {
    const email = $inputEmail.value.trim();
    const selectedPlan = document.querySelector('input[name="plan"]:checked')?.value || '';

    // Build query string safely
    const params = new URLSearchParams();

    if (mac) params.append('mac', mac);
    if (selectedPlan) params.append('plan', selectedPlan);
    if (email) params.append('email', email);
    if (ip) params.append('ip', ip);

    const finalUrl = params.toString()
      ? `${baseUrl}?${params.toString()}`
      : baseUrl;

    // Debug (optional)
    console.log('Transfer link:', finalUrl);

    $linkTranference.href = finalUrl;
  }

  // Events
  $inputEmail.addEventListener('input', updateTransferLink);
  $plans.forEach((radio) => {
    radio.addEventListener('change', updateTransferLink);
  });

  // Initialize link on page load
  updateTransferLink();

} else {
  console.warn('Required elements do not exist on this page.');
}

