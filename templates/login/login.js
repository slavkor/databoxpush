import './login.css';

           function onSuccess(googleUser) {
                console.log(googleUser);
                // get the google id_token
                var authResponse = googleUser.getAuthResponse(true);
                var id_token = authResponse.id_token;
                var access_token = authResponse.access_token;
                var client_id = document.getElementsByName('google-signin-client_id')[0].getAttribute('content');
                //send the id_token to the backend
                var dataToSend = JSON.stringify({"client_id": client_id, "token_id": id_token, "access_token":access_token,  "origin": "google"});
                fetch("{{ url_for('authlogin') }}", {
                    credentials: "same-origin",
                    mode: "same-origin",
                    method: "post",
                    headers: { "Content-Type": "application/json" },
                    body: dataToSend
                }).then(resp => {
                    if (resp.status === 200) {
                        return resp.json();
                    } else {
                        console.log("Status: " + resp.status);
                        return Promise.reject("server");
                    }
                }).then(dataJson => {
                    console.log(`Received: ${dataJson}`);

                    window.location.href = dataJson.redirect;
                }).catch(err => {
                    if (err === "server") return;
                    console.log(err);
                });
            } 
            function onFailure(error) {
                console.log(error);
            }
            function renderButton() {
                gapi.signin2.render('my-signin2', {
                  'scope': 'profile email https://www.googleapis.com/auth/analytics.readonly',
                  'width': 240,
                  'height': 50,
                  'longtitle': true,
                  'theme': 'dark',
                  'onsuccess': onSuccess,
                  'onfailure': onFailure
                });
          }