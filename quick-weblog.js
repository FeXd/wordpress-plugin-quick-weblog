function getArticle(url, api_key) {

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
      throw new Error(`Request failed with status ${response.status}`);
    }
    return response.json();
  })
	.then(response => {
    console.log(response);
  })
	.catch(err => {
    console.error(err);
  });
}
