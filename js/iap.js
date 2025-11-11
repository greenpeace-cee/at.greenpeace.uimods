CRM.$(document).ready(function () {
  window.setTimeout(checkSessionRefresh, 500);
});

function checkSessionRefresh() {
  if (Date.now() - 600 * 1000 >= parseInt(localStorage.getItem('iap_last_session_refresh'))) {
    addIapIframe();
  } else {
    window.setTimeout(checkSessionRefresh, 60000 + Math.floor(Math.random() * 5000));
  }
}

function addIapIframe() {
  const iframe = document.createElement("iframe");
  iframe.style.width = 0;
  iframe.style.height = 0;
  iframe.style.border = 0;
  iframe.style.display = 'none';
  iframe.src = CRM.vars.uimods.iap_refresh_url;
  document.body.appendChild(iframe);
  localStorage.setItem('iap_last_session_refresh', Date.now().toString());
}
