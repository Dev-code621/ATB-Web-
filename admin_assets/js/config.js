 
 
 if ("serviceWorker" in navigator) {
    window.addEventListener("load", function() {
      navigator.serviceWorker
        .register("/serviceWorker.js")
        .then(res => console.log("service worker registered", res))
        .catch(err => console.log("service worker not registered", err))
    })
  }
 

// registerServiceWorker();
const HISTORY_STORAGE_KEY = 'HISTORY_KEY';
 const getLocalHistory = () => {
    let history = JSON.parse(localStorage.getItem(HISTORY_STORAGE_KEY));
    console.log(history);
}
// getLocalHistory();

