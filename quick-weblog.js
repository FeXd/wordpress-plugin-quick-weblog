function getArticle(url, api_key) {

  if (url === "") {
    updateStatus("You  must fill out <strong>Post URL</strong> to use Auto Fill.");
    return;
  } else {
    updateStatus("Attempting to fetch information and Auto Fill...");
  }

  disableForm();

  const base_url = 'https://article-extractor2.p.rapidapi.com/article/parse?url=';

  const options = {
    method: 'GET',
    headers: {
      'X-RapidAPI-Key': api_key,
      'X-RapidAPI-Host': 'article-extractor2.p.rapidapi.com'
    }
  };

  fetch(base_url + url, options)
	.then(response => {
    if (response.status !== 200) {
      throw new Error(`Request failed with status: ${response.status}`);
    }
    return response.json();
  })
	.then(response => {
    if (response.error >= 0) {
      throw new Error(`Request received error: ${response.message}`);
    }

    console.log(response);

    document.getElementById("quick-weblog-title").value = response.data?.title;
    document.getElementById("quick-weblog-image_url").value = "Image via " + response.data?.souce;
    document.getElementById("quick-weblog-image_description").value = response.data?.title;
    document.getElementById("quick-weblog-quote").value = response.data?.description;

    updateStatus("Successfully used Auto Fill to populate form!");
    enableForm();
  })
	.catch(err => {
    updateStatus(err);
    console.error(err);
    enableForm();
  });
}

function toggleFormElements(enable) {
  const form= document.getElementById("quick-weblog");
  const formElements = form.querySelectorAll("input, select, textarea");

  formElements.forEach(element => {
    element.disabled = !enable;
  });
}

function enableForm() {
  toggleFormElements(true);
}

function disableForm() {
  toggleFormElements(false);
}

function updateStatus(status) {
  document.getElementById("quick-weblog-status").innerHTML = status;
}
