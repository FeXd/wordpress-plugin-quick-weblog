
const backup = 'https://article-extractor2.p.rapidapi.com/article/parse?url=https%3A%2F%2Frapidapi.com%2Fblog%2Frapidapi-marketplace-is-now-rapidapi-hub%2F';

function getArticle(url) {
  const options = {
    method: 'GET',
    headers: {
      'X-RapidAPI-Key': api_key,
      'X-RapidAPI-Host': 'article-extractor2.p.rapidapi.com'
    }
  };

  fetch(url, options)
	.then(response => response.json())
	.then(response => console.log(response))
	.catch(err => console.error(err));
}

console.log('quick-weblog.js success');


{/* <script type="module" src="<?php echo plugin_dir_url( __FILE__ ); ?>quick-weblog.js"></script> */}