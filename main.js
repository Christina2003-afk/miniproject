import { initializeApp } from "https://www.gstatic.com/firebasejs/11.3.0/firebase-app.js";
import { getAuth,GoogleAuthProvider,signInWithPopup} from "https://www.gstatic.com/firebasejs/11.3.0/firebase-auth.js";
import { getAnalytics } from "https://www.gstatic.com/firebasejs/11.3.0/firebase-analytics.js";
 
  const firebaseConfig = {
    apiKey: "AIzaSyAFPwJS8SfIDXGJCFsWcv7EcGw-wVLJ6wY",
    authDomain: "login-c8dd2.firebaseapp.com",
    projectId: "login-c8dd2",
    storageBucket: "login-c8dd2.firebasestorage.app",
    messagingSenderId: "672973723873",
    appId: "1:672973723873:web:458353d4a53f768f70bfa0",
    measurementId: "G-57XGH7RL6Z"
  };

  const app = initializeApp(firebaseConfig);
  const auth = getAuth(app);
  auth.languageCode = 'en'
  const provider = new GoogleAuthProvider
  const googleLogin = document.getElementById("google-login-btn");
 googleLogin.addEventListener("click",function(){
    signInWithPopup(auth, provider)
    .then((result) => {
        const credential = GoogleAuthProvider.credentialFromResult(result);
        //const token = credential.accessToken;
        const user = result.user;
        console.log(user);
        window.location.href ="index.html"; 
 })
 .catch((error) => { 
    const errorCode = error.code;
    const errorMessage = error.message;
   // const email = error.customData.email;
   // const credential = GoogleAuthProvider.credentialFromError(error);
});
})
 const analytics = getAnalytics(app);
