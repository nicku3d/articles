window.onload = function () {
  let messageBox = document.getElementById('message-box');
  messageBox.onclick = function () {
    this.style = 'display: none;';
  }
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

function editArticle()
{
  let id = document.getElementById('id').innerText;
  console.log(id);
  let title = document.getElementsByName('title')[0].value;
  let content = document.getElementsByName('content')[0].value;
  const data = "title=" + title + "&content=" + content;

  ajax('PUT', '/api/articles/' + id,  data, receiveEditResponse);
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

function receiveEditResponse()
{
  let messageBox = document.getElementById('message-box');
  messageBox.style = ""; // make box visible
  if (this.readyState === XMLHttpRequest.DONE && this.status === 200) {
    messageBox.textContent = 'Sucessfully edited article!';
    setTimeout(() => document.location.href = '/' , 3000);
  } else {
    // wyświetlić błąd
    messageBox.textContent = 'Article not found!';
  }
}

function receiveApiResponse()
{
  let messageBox = document.getElementById('message-box');
  messageBox.style = ""; // make box visible
  if (this.readyState === XMLHttpRequest.DONE && this.status === 200) {
    const response = JSON.parse(this.responseText)
    const messageBox = document.getElementById('message-box');
    messageBox.textContent = response.message + ' Site will be refreshed in 3 sec.';
    //czekamy 3 sekundy i odświeżamy na stronę główną - do zmiany w przyszłości
    setTimeout(() => document.location.href = '/' , 3000);
  } else {
    // wyświetlić błąd
    messageBox.textContent = 'Error while communicating with server! Server response: ' + this.responseText;
  }
}