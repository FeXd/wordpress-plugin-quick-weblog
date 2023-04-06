function fetchArticleData(url, api_key) {
  const base_url = 'https://article-extractor2.p.rapidapi.com/article/parse?url=';
  const options = {
    method: 'GET',
    headers: {
      'X-RapidAPI-Key': api_key,
      'X-RapidAPI-Host': 'article-extractor2.p.rapidapi.com'
    }
  };

  return fetch(base_url + url, options)
    .then(response => {
      if (response.status !== 200) {
        throw new Error(`Request failed with status: ${response.status}`);
      }
      return response.json();
    })
    .then(response => {
      if (response.error > 0) {
        throw new Error(`Request received error: ${response.message}`);
      }
      return response.data;
    })
    .catch(err => {
      throw new Error(`Error fetching article data: ${err.message}`);
    });
}

function updateFormFields(data) {
  document.getElementById("quick-weblog-title").value = data?.title;
  document.getElementById("quick-weblog-image_url").value = data?.image;
  document.getElementById("quick-weblog-image_description").value = "image via " + data?.source;
  document.getElementById("quick-weblog-quote").value = data?.description;
}

function fetchAndPopulateFormFields(articleUrl, rapidApiKey) {
  if (articleUrl === "") {
    updateStatus("You must fill out <strong>Post URL</strong> to use Auto Fill.");
    return;
  }

  if (rapidApiKey === "") {
    updateStatus("Your API Key is missing. Please update settings to use Auto Fill.");
    return;
  }

  updateStatus("Attempting to fetch information and Auto Fill...");
  disableForm();

  fetchArticleData(articleUrl, rapidApiKey)
    .then(data => {
      updateFormFields(data);
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
  const form = document.getElementById("quick-weblog");
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
