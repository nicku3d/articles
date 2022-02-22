window.onload = function () {
  console.log('susiak zaladowano');
};


function ajax(method = 'POST', url, data,func)
{
  let request = new XMLHttpRequest();
  request.addEventListener('load', func)
  request.open(method, url);
  if (data) {
    request.send(data);
  } else {
    request.send();
  }

}

function testAjax()
{
  let data = new FormData();
  ajax('GET', '/api/articles/3', data, receiveApiResponse)
}

function editArticle()
{
  let title = document.getElementsByName('title')[0].value;
  let content = document.getElementsByName('content')[0].value;
  const data = "title=" + title + "&content=" + content;

  ajax('PUT', '/api/articles/' + id,  data, receiveApiResponse);
}

function createArticle()
{
  let title = document.getElementsByName('title')[0].value;
  let content = document.getElementsByName('content')[0].value;

  const data = "title=" + title + "&content=" + content;
  ajax('POST', '/api/articles/', data, receiveApiResponse)
}

function deleteArticle(id)
{
  ajax('DELETE', '/api/articles/' + id, null, receiveApiResponse)
}

function receiveApiResponse()
{
  if (this.readyState === XMLHttpRequest.DONE && this.status === 200) {
    console.log(this);
    console.log(this.responseText);
    console.log(JSON.parse(this.responseText));
  } else {
    // wyświetlić błąd
    console.log(this);
    console.log('Error while communicating with server! Server response: ' + this.responseText);
  }
}