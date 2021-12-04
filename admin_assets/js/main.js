const doc = document.documentElement;
const appHeight = () => {
   doc.style.setProperty('--app-height', `${window.innerHeight}px`);
}
appHeight();
window.addEventListener('resize', () => {
   appHeight();
});

// SHOW/HIDE PASSWORD
const togglePassword = document.querySelector('#password-input i');
const password = document.querySelector('#password-input input');
if(togglePassword !== null) {
   togglePassword.addEventListener('click', function (e) {
      const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
      password.setAttribute('type', type);
      this.classList.toggle('fa-eye-slash');
   });
}

// SHOW AND CLOSE MODAL FUNCIONS 
const modals = document.querySelectorAll("[data-modal");
for (const modal of modals) {
   modal.addEventListener("click", function(e) {
      e.preventDefault();
      const modalId = this.dataset.modal;
      document.getElementById(modalId).classList.add("show");
      document.body.classList.add('modal-open')
   });
}
const modalsClose = document.querySelectorAll("[data-close");
for (const modal of modalsClose) {
   modal.addEventListener("click", function() {
      const modalId = this.dataset.close;
      console.log(modalId);
      document.getElementById(modalId).classList.remove("show");
      document.body.classList.remove('modal-open')
   });
}

// SHOW TABS FUNCTION
const tabsOpen = document.querySelectorAll("[data-tab]");
const tabsContent = document.querySelectorAll("[data-tabcontent]");
for (const tab of tabsOpen) {
   const navTab = document.getElementsByClassName('tabs-container')[0];
   tab.addEventListener("click", function() {
      for(const t of tabsOpen) {
         navTab.classList.remove(t.dataset.tab);
         t.classList.remove('active');
      }
      // add class to parent container to style the background animation
      navTab.classList.add(this.dataset.tab);
      // show tab
      for(const tabContent of tabsContent) {
         const dataTabContent = tabContent.dataset.tabcontent;
         if(dataTabContent === tab.dataset.tab) {
            tabContent.style.display = 'block';
            tabContent.classList.add("show");
            this.classList.add('active');
         } else {
            tabContent.style.display = 'none';
            tabContent.classList.remove("show");
         }
      }

      /* CHANGE THE INFORMATION ABOVE TAB [STATISTICS] */
      if(navTab.getAttribute('id') === 'reportedPostsTab') {
         const notificationContainer = document.getElementById('reportedPostNotification');
         notificationContainer.classList.remove('closed');
         notificationContainer.classList.remove('ignored');
         notificationContainer.classList.remove('open');
         notificationContainer.classList.add(this.dataset.tab);
      }
   });
}


let chatTextarea = document.getElementById('messageInput');
if(chatTextarea !== null ) {
   chatTextarea.addEventListener('keyup', function() {
      this.style.height = this.scrollHeight + 'px';
   });
}

function submitMsg() {
   // e.preventDefault();
   let msg = chatTextarea.value;
   let container = document.querySelectorAll('.messages-content')[0];
   container.innerHTML += `<div class="chat-message outcome">${msg}</div>`;
   chatTextarea.value = '';
   chatTextarea.style.height = 20 + 'px';
} 


let chatCheckBoxes = document.querySelectorAll('.contact-item input');
if(chatCheckBoxes !== null ) {
   for(const checkbox of chatCheckBoxes) {
      checkbox.addEventListener('change', function() {
         checkbox.parentElement.classList.toggle('selected');
      })
   }
}