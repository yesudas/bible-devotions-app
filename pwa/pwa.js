if ('serviceWorker' in navigator) {
    navigator.serviceWorker.register('sw.js')
        .then(() => console.log("Service Worker Registered"))
        .catch(err => console.log("Service Worker Failed:", err));
}

document.addEventListener("DOMContentLoaded", () => {  
  // PWA functionality
  const installAppBtn = document.getElementById("installAppBtn");
  let deferredPrompt;

  // Hide install button initially
  if (installAppBtn) {
    installAppBtn.style.display = "none";
  }

  window.addEventListener("beforeinstallprompt", (e) => {
    console.log("beforeinstallprompt event fired");
    e.preventDefault();
    deferredPrompt = e;
    
    try {
        const urlParams = new URLSearchParams(window.location.search);
        // Show install button if not launched from installed app
        if (urlParams.get('f') !== 'app' && installAppBtn) {
            installAppBtn.style.display = "inline-block";
            console.log("Install button shown");
        }
    } catch (e) {
        console.error("Error managing install button visibility:", e);
    }
  });

  // Check if app is already installed
  window.addEventListener('appinstalled', () => {
    console.log('PWA was installed');
    if (installAppBtn) {
      installAppBtn.style.display = "none";
    }
  });

  if (installAppBtn) {
    installAppBtn.addEventListener("click", async () => {
      if (deferredPrompt) {
        deferredPrompt.prompt();
        const choice = await deferredPrompt.userChoice;
        console.log("User choice:", choice.outcome);
        if (choice.outcome === 'accepted') {
          installAppBtn.style.display = "none";
        }
        deferredPrompt = null;
      } else {
        console.log("No deferred prompt available");
      }
    });
  }

});
