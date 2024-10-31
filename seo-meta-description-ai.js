"use strict";

document.addEventListener("DOMContentLoaded", function() {
	
	/** */
	function getActivationCode() {

		const element = document.getElementById("seo-meta-description-ai-activation-code");
		return element.value;
	}

	/** */
	function showMetaContent(response) {
	    
	    const json            = JSON.parse(response);
	    const message         = json.message;
        const metaDescription = json.meta_description;

		const textarea = document.getElementById("seo-meta-description-ai-textarea");
		
		if (metaDescription !== undefined) {
			
			let stripped   = metaDescription.replace(/^"(.*)"$/, "$1");
			stripped       = stripped.replace(/\s+/g, " ");
			textarea.value = stripped;
			
			updateLabel(textarea.value.length);
			
		} else {
			textarea.value = message;
		}
	}

	/** */
	function getMetaContent(content) {

		const URL = "https://wp01.binarysolutions.biz/get_meta_content";
		//const URL = "http://127.0.0.1:5000/get_meta_content";
		const xhr = new XMLHttpRequest();

		xhr.open("POST", URL, true);
		xhr.setRequestHeader("Content-Type", "application/json; charset=UTF-8");
		xhr.onreadystatechange = function() {
			
			if (xhr.readyState != 4 || xhr.status != 200) {
				return;
			}
			
			const response = xhr.responseText;
			if (response.length < 1) {
				return;
			}
			
			showMetaContent(response);
		}
		
		const body = { 
			version:         data.version,
			host_name:       data.host_name,
			activation_code: getActivationCode(),
			content:         data.content,
		};
		
		const jsonData = JSON.stringify(body);
		xhr.send(jsonData);
	}

	/** */	
	function updateLabel(length) {
		
		const label = document.getElementById("seo-meta-description-ai-label");
		const span  = label.getElementsByTagName("span")[0];
		
		if (length !== undefined) {
			span.textContent = length;
		}
		
		if (span.textContent >= 110 && span.textContent <= 160) {
		    label.style.color = "green";
		} else {
		    label.style.color = "red";
	  	}		
	}
	
	/** */
	function setTextAreaListener() {
		
	    const textarea = document.getElementById("seo-meta-description-ai-textarea");
		textarea.addEventListener("input", function() {
		    updateLabel(textarea.value.length);
	  	});	    
	}
	
	/** */
	function setButtonListener() {
			
	    const button   = document.getElementById("seo-meta-description-ai-button");
	    const textarea = document.getElementById("seo-meta-description-ai-textarea");
	    
	    button.addEventListener("click", function() {
		
	        if (typeof data === "undefined") {
	            return;
	        }
	        
	        if (!data.content) {
				textarea.value = "No content detected. Please save draft or publish post first.";
	            return;
	        }
	        
			textarea.value = "Generating description ...";
			updateLabel(0)
			getMetaContent();
	    });
	}	
	
	updateLabel();
	setTextAreaListener();
	setButtonListener();
});
