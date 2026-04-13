//listen to submit button 
const form = document.getElementById('validateForm');
const message = document.getElementById('errorMsg');
//event lister for login
form.addEventListener('submit', function (event) {
	message.innerHTML = "";
        //do not send php request yet
        event.preventDefault();
	//did user enter nothing?
	//did user meet requirements for email
	//did user meet password requirements
	//input is vaild
        if(validateInput()){
                this.submit();
        }else{
		//send specific message to user
                //let function send message
        }

});


//validate email input
function validateInput(){
	//clear 
        //get user input
        const emailInput = document.getElementById("email").value;
        const passwordInput = document.getElementById("password").value;

	//did user enter anything?
	if(emailInput === "" && passwordInput === ""){
		message.innerHTML = "PLEASE ENTER EMAIL AND PASSWORD";
		return false;
	}else if(emailInput === ""){
                message.innerHTML = "PLEASE ENTER EMAIL";
		return false;
        }else if(passwordInput === ""){
                message.innerHTML = "PLEASE ENTER PASSWORD";
		return false;
        }

        //email and password regex
        //password:
        //validate password input
        //password length 8
        //at least one capital letter
        //at least one special character
        //at least one digit
        //no spaces
        const emailRegex = /^([a-zA-Z0-9._%-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,6})$/;
        const passwordRegex = /^(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9])(?=.*[^a-zA-Z0-9])\S{8,}$/;

	//check validation individually to send specific messages to user
	
	//invalid email check
	//is there an @
	if(!emailInput.match(/@/)){
		message.innerHTML += "MISSING @";
		message.innerHTML += "<br>";
	}

	//is there an input before @ 
	if(!emailInput.match(/[a-zA-Z0-9]+@/)){
                message.innerHTML += "MISSING INPUT BEFORE THE @";
                message.innerHTML += "<br>";
        }
	//is there an input before @
        if(!emailInput.match(/@[a-zA-Z0-9]+/)){
                message.innerHTML += "MISSING INPUT AFTER THE @";
                message.innerHTML += "<br>";
        }

	//is there an .com or .org ...
	if(!emailInput.match(/\.[a-zA-Z]{2,6}/)){
                message.innerHTML += "MISSING DOMAIN EXTENSION";
                message.innerHTML += "<br>";
        }

	//invalid password check
	if(!passwordInput.match(passwordRegex)){
		//tell the user exactly what is wrong

		//if user enter spaces
		if(passwordInput.match(/[^\S]/)){
                        message.innerHTML += "NO SPACES";
                        message.innerHTML += "<br>";
                }
		//password too short
		if(passwordInput.length < 8){
			message.innerHTML += "PASSWORD TOO SHORT";
                	message.innerHTML += "<br>";
		}
		//capital letter check
		if(!passwordInput.match(/[A-Z]/)){
			message.innerHTML += "MISSING CAPITAL LETTER";
                        message.innerHTML += "<br>";
		}
		//lower case check
		if(!passwordInput.match(/[a-z]/)){
                        message.innerHTML += "MISSING LOWERCASE LETTER";
                        message.innerHTML += "<br>";
                }
		//digit check
		if(!passwordInput.match(/[0-9]/)){
                        message.innerHTML += "MISSING DIGIT";
                        message.innerHTML += "<br>";
                }
		//special character check
		if(!passwordInput.match(/[^a-zA-Z0-9\s]/)){
                        message.innerHTML += "MISSING SPECIAL CHARACTER";
                        message.innerHTML += "<br>";
                }
	}

        return emailInput.match(emailRegex) && passwordInput.match(passwordRegex);

}

