// NOTE: the api is not RESTful, but I try to follow what few REST rules I know

// prepare the variables
const form_content = document.querySelector("#form-content");
const login_form_content = document.querySelector("#login_form_content");
const login_with_email_btn = document.querySelector(".login_with_email_btn")
const login_btn = document.querySelector("#login_btn")
const login_form_container = document.querySelector("#login_form_container")
const content_container = document.querySelector("#content_container")
const notice_container = document.querySelector("#notice_container")
const password_container = document.querySelector(".password_container")
let fetch_url = '', send_data = false, userKey, sendKey = false, tasks_to_delete = [], fetch_callable = true, gKey = false;


document.addEventListener("DOMContentLoaded", function(){
	createAjaxLinks();
	mtd_login();

	// login_with_email_btn.addEventListener("click", function(){
	// 		login_with_email_btn.classList.add("hidden")
	// 		login_form_content.classList.remove("hidden")
	// 		login_form_content.classList.add("open")
	// 	prepareForm();
	// })

});


// function onSignIn(googleUser) {
// 	var profile = googleUser.getBasicProfile();
// 	gKey = googleUser.getAuthResponse().id_token;
// 	mtd_login();
// }

// function signOut() {
// 	var auth2 = gapi.auth2.getAuthInstance();
// 	auth2.signOut().then(function () {
// 		console.log('User signed out.');
// 	});
// }


function mtd_login() {

	if (login_with_email_btn) {
		login_with_email_btn.addEventListener("click", function(){
			if (login_with_email_btn.classList.contains("hidden")) {
				login_with_email_btn.classList.remove("hidden")
				login_form_content.classList.add("hidden")
			} else {
				login_with_email_btn.classList.add("hidden")
				login_form_content.classList.remove("hidden")
			}

		})
	}

	// if a user key exists in local storage, this is probably a returning visitor
	// validate it's format, make an ajax call to get the primary view, and then prepare any forms on the page
	if (getCookie("mtdUserKey") != null || localStorage.getItem("mtdUserKey") != null) {

		if (getCookie("mtdUserKey") != null) {
			sendKey = getCookie("mtdUserKey");
		} else {
			sendKey = setCookie('mtdUserKey', localStorage.getItem("mtdUserKey"))
		}
		// if (gKey) {
		// 	sendKey = setCookie('mtdUserKey', gKey)
		// }

		if (sendKey) {
			userKey = sendKey
			fetch_url = './../user/primaryView/' + userKey
			ajaxCall(fetch_url, false, true);
		} else {
			createError(101);
			return;
		}



		prepareForm();

	// if a user key does not exist in local storage, this is probably a new visitor so we'll create a new id for them, 
	// and then load up the first-time primary view. This is all handled on the back end
	} else {
		fetch_url = './../user/new/';
		ajaxCall(fetch_url)
	}

	console.log("fetch_url: " + fetch_url)
}





// makes all the links we created on the page work with fetch
function createAjaxLinks() {
	ajax_links = document.querySelectorAll(".f_link")
	ajax_links.forEach(function(link){

		if (!link.classList.contains("form_submit")) {
			link.addEventListener('click', function(e){
				e.preventDefault();

				if (!link.hasAttribute("disabled") && link.getAttribute("href")) {

					if (content_container.children.length > 0) {
						content_container.innerHTML = '';
					}
					fetch_url = link.getAttribute("href")
					ajaxCall(fetch_url, false)
				}
			})
		}
	})
}

// disables all those fetch links so they can't spam the server while it's getting a request
function disableAjaxLinks() {
	ajax_links = document.querySelectorAll(".f_link")
	ajax_links.forEach(function(link){
		link.setAttribute("disabled", "")
	})
}

// re-enables those same links and adds functionality to any notices that cropped up
function enableAjaxLinks() {
	ajax_links = document.querySelectorAll(".f_link")
	ajax_links.forEach(function(link){
		if (link.hasAttribute("disabled")) {
			link.removeAttribute("disabled")
		}
	})

	removable_notices = document.querySelectorAll(".removeable_BE_notice")
	if (removable_notices.length > 0) {
		removable_notices.forEach(function(notice){
			notice.addEventListener("click", function(){
				notice.parentElement.removeChild(notice)
			})
		})
	}
}

// creates the loading circle
function loading() {
	loading_exists = document.querySelector("#loading")
	if (!loading_exists) {
		loading_text = document.createElement("span")
		loading_text.classList = "sr-only"


		loading_elem = document.createElement("p")
		loading_elem.classList = "spinner-border text-info m-5 p-5"
		loading_elem.setAttribute("role", "status")
		loading_elem.append(loading_text)

		loading_container = document.createElement("div")
		loading_container.classList = "text-center"
		loading_container.setAttribute("id", "loading")
		loading_container.append(loading_elem)

		content_container.classList.add("sr-only")
		append_to_page = document.querySelector("main")
		append_to_page.insertBefore(loading_container, content_container)
	}
}

// removes the loading circle
function removeLoading() {
	loading_exists = document.querySelector("#loading")
	if (loading_exists) {
		content_container.classList.remove("sr-only")
		loading_elem = document.querySelector("#loading")
		loading_elem.parentElement.removeChild(loading_elem)
	}
}


// AJAX CALL():
// make a fetch/ajax call
// refuse any new fetch calls until it's done with the current one to prevent server spam
// disable ajax links for a similar reason
// show the loading screen
// attempt to make the call, showing an error if it results in a 404
// if that went through ok, turn the resulting response into html and check for any notices
// generate any notices and display the rest of the html
function ajaxCall(fetch_url, body = false) {
		disableAjaxLinks();
		loading();

		fetch(fetch_url, {
			method: 'POST',
		    headers: {
		      'Accept': 'text/html, application/json',
		      'Content-type': 'application/json',
		    },

		    // if it gets redirected, follow the page. Htaccess is rerouting so we can have RESTful urls
		    redirect: 'follow',
			body: body,
			cors: "same-origin",
			credentials: 'include'
		}).then((response) => {
			if (response.status == 404) {
				createError(103);
				data = false;
			} else {
				data = response.text();
			}
			return data;
		}).then((data) => {
			if (!data) {
				return;
			}
			// console.log(data)
			const doc = new DOMParser().parseFromString(data, 'text/html');

			check_if_notice = doc.querySelector("#generateNotice")
			verifyInfo = doc.querySelector("#verifyInfo")
			if (verifyInfo) {
				if (verifyInfo.getAttribute('data-key')) {
					const key = verifyInfo.getAttribute('data-key');
					if (isInt(key) && key.length === 7) {
						// If I keep this as a portfolio piece, I'd change this to have a login system
						setCookie('mtdUserKey', key)
						localStorage.setItem('mtdUserKey', key)
					} else {
						createError(101);
					}
					verifyInfo.parentElement.removeChild(verifyInfo)

					addToHtml(doc)
				}

				if (verifyInfo.getAttribute('data-user-exists')) {
					prepareLoginForm();
					return;
				}

			} else if (check_if_notice) {
				notice_type = check_if_notice.getAttribute("data-notice-type")
				notice_text = check_if_notice.getAttribute("data-notice-text")
				if (!notice_text) {
					error_code = check_if_notice.getAttribute("data-code")
					createError(error_code);
					console.log('error code: ' + error_code)
				} else {
					createNotice(notice_text, notice_type)
				}
				createAjaxLinks();
				enableAjaxLinks();
			} else {
				addToHtml(doc)
			}
		})

}

// builds the page using the html from the server
function addToHtml(doc) {
	setTimeout(function(){
		removeLoading();

		if (content_container.children.length > 0) {
			content_container.innerHTML = '';
		}

		doc.body.childNodes.forEach(function(e){
			content_container.append(e);
		})

		createAjaxLinks();
		enableAjaxLinks();
		prepareForm();
	}, 300);
}

// prep any forms on the page
function prepareForm() {

	delete_btn_containers = document.querySelectorAll("[data-delete]")
	form = document.querySelector("form")
	all_inputs = document.querySelectorAll(".form-control")
	form_submit = document.querySelector(".form_submit")

	if (form) {
		form_submit.addEventListener('click', function(e){
			e.preventDefault();

			let form_info = {};
			for (var i = 0; i < all_inputs.length; ++i) {
			  	form_info[all_inputs[i].name] = all_inputs[i].value
			}
			send_form = JSON.stringify(form_info)
			href = form.getAttribute("data-send-to")

			if (form.classList.contains("login_form_content")) {
				let email_input = document.querySelector("#email")
				if (email_input.validity) {
					href = href + form_info["email"].replace(/\s/g, "")
				} else {
					trigger_form_error("email")
					return;
				}
			}

			ajaxCall(href, send_form);
		})

		required_inputs = document.querySelectorAll("input[required]")
		required_inputs.forEach(function(input){
			input.addEventListener("blur", function(){

				// we're gonna go a very strict but time tested method of validation and deny anything abnormal. No smiley faces allowed :/
				text_regex = /[^a-zA-Z0-9_ ,\.-]/
				date_regex = /[^a-zA-Z0-9\/-]/
				pwd_regex = /^(\w*(?=\w*\d)(?=\w*[a-z])(?=\w*[A-Z])^\w*)\w{6,}$/

				if (input.hasAttribute("required")) {
					console.log("req input: " + input)
					switch(input.getAttribute("type")) {
					  	case 'text':
					  		response = input.value

					  		if (response != "") {
						    	clean_response = response.replace(text_regex, '')
						    	if (clean_response !== response) {
					  				let input_id = input.getAttribute("id")
						    		trigger_form_error(input_id)
						    	} else {
						    		error_exists = input.classList.contains("border-danger")
						    		if (error_exists) {
						    			input.classList.remove("border-danger")
						    		}
						    	}
						    }
					    break;

					  	case 'password':
					  		response = input.value

					  		if (response != "") {
						    	clean_response = response.replace(pwd_regex, '')
						    	if (clean_response !== response) {
					  				let input_id = input.getAttribute("id")
						    		trigger_form_error(input_id)
						    	} else {
						    		error_exists = input.classList.contains("border-danger")
						    		if (error_exists) {
						    			input.classList.remove("border-danger")
						    		}
						    	}
						    }
					    break;

						case 'date':
			  				response = input.value
					  		if (response != "") {
						    	clean_response = response.replace(date_regex, '')
						    	if (clean_response !== response) {
						    		trigger_form_error(input.getAttribute("id"))
						    	} else {
				    				error_exists = input.classList.contains("border-danger")
						    		if (error_exists) {
						    			input.classList.remove("border-danger")
						    		}
						    	}
						    }
					    break;
						default:
							break;
					}
				}

			})

		})
	// if we're on the delete page, we'll need to do a little more.
	} else if (delete_btn_containers.length > 0) {
		tasks_to_delete = []
		delete_btn_containers.forEach(function(delete_btn_container){
			let t_key = delete_btn_container.getAttribute("data-key")
			let search_for_btn = '[data-key="' + t_key + '"] .mark-task-btn'
			let delete_btn = document.querySelector(search_for_btn)
			delete_btn_container.addEventListener("click", function(){
				let key = delete_btn_container.getAttribute("data-key")
				if (delete_btn.classList.contains("bg-info")) {
					let key_index = tasks_to_delete.indexOf(key)
					tasks_to_delete.splice(key_index, 1)
					delete_btn.classList.remove("bg-info");
				} else {
					tasks_to_delete.push(key)
					delete_btn.classList.add("bg-info");
				}
			})
		})

		delete_selected_btn = document.querySelector("#delete_tasks")
		if (delete_selected_btn) {
			delete_selected_btn.addEventListener("click", function(){
				if (tasks_to_delete.length > 0) {
					fetch_url = "./../task/delete/" + userKey
					ajaxCall(fetch_url, JSON.stringify(tasks_to_delete))
				} else {
					createError(102)
				}

			})
		}
	}
}

// changes the colors on an invalid input
function trigger_form_error(input_id) {
	input = document.querySelector("#" + input_id)
	input.classList.add("border-danger")
}

// checks if the given value is a number
function isInt(value) {
  return !isNaN(value) && 
         parseInt(Number(value)) == value && 
         !isNaN(parseInt(value, 10));
}

// sets a cookie
function setCookie(name,value,days) {
    var expires = "";
    if (days) {
        var date = new Date();
        date.setTime(date.getTime() + (days*24*60*60*1000));
        expires = "; expires=" + date.toUTCString();
    }
    document.cookie = name + "=" + (value || "")  + expires + "; path=/";
}

// gets a cookie
function getCookie(name) {
    var nameEQ = name + "=";
    var ca = document.cookie.split(';');
    for(var i=0;i < ca.length;i++) {
        var c = ca[i];
        while (c.charAt(0)==' ') c = c.substring(1,c.length);
        if (c.indexOf(nameEQ) == 0) return c.substring(nameEQ.length,c.length);
    }
    return null;
}



// ----------------------->notice and error handling

// removes a notice
function removeNotice(e) {
	notice_to_remove = e.srcElement;
	notice_to_remove.parentElement.removeChild(notice_to_remove)

	let lingering_div = document.querySelector("[data-notice-remove]")
	if (lingering_div) {
		lingering_div.parentElement.removeChild(lingering_div)
	}
}

// generates a notice
// it's in a class because I've never worked with js classes before and...
// I wanted to understand the differences between js and php classes
class notice_obj {
	// sets up all the variables
	constructor(notice_text, notice_type, notice_code) {
		this.notice_code = notice_code;
		this.notice_text = notice_text;
		this.notice_type = notice_type;
		this.notice_container = document.querySelector("#content_container");
		this.preparedNotice;
		this.notice_id = 'n_' + Math.floor(Math.random() * 10) +Math.floor(Math.random() * 10) + Math.floor(Math.random() * 10)
	}

	// creates a notice, uses the bootstrap alert system for styling, and returns the actual notice so it can be further edited before being shown
	createNotice() {
		let loading_exists = document.querySelector("#loading")
		if (loading_exists) {
			content_container.classList.remove("sr-only")
			loading_elem = document.querySelector("#loading")
			loading_elem.parentElement.removeChild(loading_elem)
		}
		let notice_container_centering = document.createElement('div');
		let notice_element = document.createElement('p');
		let search_code = '#e' + parseInt(this.notice_code);
		let check_if_exists = document.querySelector(search_code)
		if (!check_if_exists) {
			let code = 'e' + this.notice_code
			notice_container_centering.setAttribute('id', code);
		} else {
			return false;
		}
		notice_element.classList = 'm-0 alert alert-' + this.notice_type;
		notice_element.setAttribute('role', 'alert');

		let notice_text_node = document.createTextNode(this.notice_text);
		notice_element.appendChild(notice_text_node);


		notice_container_centering.setAttribute('data-notice-remove', 'true');
		notice_container_centering.classList = "container"
		notice_container_centering.appendChild(notice_element);

		this.preparedNotice = notice_container_centering;
		return this.preparedNotice;
	}

	// adds the finished notice to the html
	// seperated this out so I could edit the notice if needed before showing it to the user
	appendNotice() {
		if (this.preparedNotice) {
			notice_container.append(this.preparedNotice);
			return this.preparedNotice.getAttribute("id");
		} else {
			return false;
		}
	}

}


// creates an error, this had to be separate from the class so the notice could call on removeNotice when clicked. This is only for js generated errors
// it also generates the appropriate error text using the error code
function createError(error_code) {
	switch (parseInt(error_code)) {
		case 101:
			error_text = "Invalid user key, please contact our service team."
			break;

		case 102:
			error_text = "Please select at least one task to delete."
			break;

		case 103:
			error_text = "Hmm, that link doesn't seem to exist. Please contact our service team."
			break;

		case 201:
			error_text = "Invalid user key, please contact our service team."
			break;

		case 202:
			error_text = "Could not connect to database, please try again later."
			break;

		default:
			error_text = "Something went wrong...please contact our service team."
			break;
	}

	new_notice = new notice_obj(error_text, 'danger', error_code);
	create_notice = new_notice.createNotice()
	if (create_notice) {
		notice_id = "#" + new_notice.appendNotice(create_notice)
		get_notice = document.querySelector(notice_id) 
		get_notice.addEventListener('click', function(notice_id){
			removeNotice(notice_id)
		})
	}
}

// creates a notice, primarily used by the api
function createNotice(notice_text, notice_type = 'warning') {
	new_notice = new notice_obj(notice_text, notice_type, 000)
	create_notice = new_notice.createNotice()
	if (create_notice) {
		notice_id = "#" + new_notice.appendNotice(create_notice)
		get_notice = document.querySelector(notice_id) 
		get_notice.addEventListener('click', function(e){
			removeNotice(e)
		})
	}

}




// -------------------------------------------->
// ----------------->DEBUG COMMANDS----------------->
// -------------------------------------------->
// you can use these if you want to trigger a specific response, like an error


// generates an error with default error text, can be JS error or back end error (you can see the results of this in the database under logs if you generate a back end error)
function generate_error(front_end_only = true) {
	if (front_end_only === true) {
		createNotice('Debug error, js errors usually look the same as back end errors.', 'warning', 110)
	} else if (front_end_only === false) {
		fetch_url = './../task/nonsense/'
		ajaxCall(fetch_url, false);
	}
}

// sets the user to one that already has tasks
function get_preloaded_tasks() {
	setCookie('mtdUserKey', 7152056)
	localStorage.setItem('mtdUserKey', 7152056)
}


