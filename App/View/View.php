<?php

namespace App\View;

use App\Model\Article;
use HtmlGenerator\HtmlTag;

class View
{
    private string $site = 'articleList';

    /**
     * @var Article[]
     */
    private array $data = [];

    private string $message = '';


    public function print()
    {
        echo $this->getHtml();
        die;
    }

    public function getHtml() : HtmlTag
    {
        $html = HtmlTag::createElement('html');
        $html->addElement($this->getHead())
            ->addElement($this->getBody());
        return $html;
    }

    public function getHead() : HtmlTag
    {
        $head = HtmlTag::createElement('head');
        $head->addElement($this->getBootstrapLink());
        $head->addElement($this->getBootstrapScript());
        foreach ($this->getScripts() as $script) {
            $scriptElement = HtmlTag::createElement('script');
            $scriptElement->set('src', $script);
            $head->addElement($scriptElement);
        }
        return $head;
    }

    public function getBootstrapLink() : HtmlTag
    {
        $bootstrapLink = HtmlTag::createElement('link');
        $bootstrapLink->set('href','https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css')
            ->set('rel', 'stylesheet')
            ->set('integrity', 'sha384-1BmE4kWBq78iYhFldvKuhfTAU6auU8tT94WrHftjDbrCEXSU1oBoqyl2QvZ6jIW3')
            ->set('crossorigin', 'anonymous');
        return $bootstrapLink;
    }

    public function getBootstrapScript() : HtmlTag
    {
        $bootstrapScript = HtmlTag::createElement('script');
        $bootstrapScript->set('src', 'https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js')
            ->set('integrity', 'sha384-ka7Sk0Gln4gmtz2MlQnikT1wXgYsOg+OMhuP+IlRH9sENBO0LRn5q+8nbTov4+1p')
            ->set('crossorigin', 'anonymous');
        return $bootstrapScript;
    }

    public function getScripts() : array
    {
        return ['/js/main.js'];
    }

    public function setErrorMessage(string $message) : self
    {
        $this->message = $message;
        return $this;
    }

    /**
     * @param string $site
     * @return $this
     */
    public function setSite(string $site) : self
    {
        $this->site = $site;
        return $this;
    }

    /**
     * @param array $data
     * @return $this
     */
    public function setData(array $data) : self
    {
        $this->data = $data;
        return $this;
    }

    public function getBody() : HtmlTag
    {
        $body = HtmlTag::createElement('body');
        $body->addElement($this->getNavBar())
            ->getParent()
            ->addElement($this->getMessageBox())
            ->getParent()
            ->addElement($this->getBodyContent()); //TODO action czy coś pewnie jako property
        return $body;
    }

    public function getMessageBox() : HtmlTag
    {
        $box = HtmlTag::createElement('div');
        $box->addClass('alert')
            ->addClass('alert-primary')
            ->set('id', 'message-box')
            ->set('style', 'display:none;')
            ->set('role', 'alert');

        return $box;
    }

    public function getNavBar() : HtmlTag
    {
        $nav = HtmlTag::createElement('nav');
        $nav->addClass('navbar')
            ->addClass('navbar-expand-lg')
            ->addClass('navbar-light')
            ->addClass('bg-light')
            ->addElement('a')
            ->addClass('navbar-brand')
            ->set('href', '/')
            ->text('Articles')
            ->getParent()
            ->addElement('a')
            ->addClass('nav-item')
            ->addClass('nav-link')
            ->set('href', '/')
            ->text('Articles list')
            ->getParent()
            ->addElement('a')
            ->addClass('nav-item')
            ->addClass('nav-link')
            ->set('href', '/article/add')
            ->text('Create Article');

        return $nav;
    }

    public function getBodyContent() : HtmlTag
    {
        return match ($this->site) {
            'errorMessage' => $this->getErrorMessage(),
            'articleView' => $this->getArticleView(),
            'articleAddForm' => $this->getArticleForm(),
            'articleEditForm' => $this->getArticleForm('edit'),
            default => $this->getArticleListView(),
        };
    }

    public function getErrorMessage() : HtmlTag
    {
        $message = $this->message ?: 'Error, no error message!';
        $errorDiv = HtmlTag::createElement('div');
        $errorDiv->addClass('alert')
            ->addClass('alert-danger')
            ->set('role', 'alert')
            ->text($message);
        return $errorDiv;
    }

    public function getArticleView() : HtmlTag
    {
        $article = reset($this->data);
        $this->validateArticle($article);
        //TODO jakieś opcje edytuj usuń byłby przydatne
        $div = HtmlTag::createElement('div');
        $div->addElement('h2')
            ->text($article->getTitle())
            ->getParent()
            ->addElement('div')
            ->text($article->getContent());
        return $div;
    }

    private function validateArticle($article) : void
    {
        // jeśli kod działa właściwie to nigdy nie powinno tu wejść
        if (!($article instanceof Article)) {
            print 'Error! Invalid data given!'; // TODO można tu wyświetlić za pomocą tej klasy error po prostu
            die;
//            throw new InvalidArgumentException('Przekazano błędne dane do obiektu View', 666);
        }
    }

    public function getArticleListView() : HtmlTag
    {
        $articleList = HtmlTag::createElement('table');
        $articleList->addClass('table')
            ->addElement('thead')
            ->addElement('th')->text('title')
            ->getParent()
            ->addElement('th')->text('content')
            ->getParent()
            ->addElement('th')->text('options');

        $tbody = HtmlTag::createElement('tbody');
        foreach ($this->data as $article) {
           $this->validateArticle($article);

           //TODO getEditArticleLink(), getViewArticleLink, getDeleteArticleLink lub buttony, buttony lepiej by wygladały pewnie
           $edit = HtmlTag::createElement('button');
           $edit->addElement('a')
               ->set('href', '/article/edit/' . $article->getId())
               ->text('edit');
           $delete = HtmlTag::createElement('button');
           $delete->text('delete')
               ->set('onclick', "deleteArticle({$article->getId()})");

           $view = HtmlTag::createElement('button');
           $view->addElement('a')
               ->set('href', 'article/view/' . $article->getId())
               ->text('view');

            $tbody->addElement('tr')
                ->addElement('td')->text($article->getTitle())
                ->getParent()
                ->addElement('td')->text($article->getContent())
                ->getParent()
                ->addElement('td')->text("{$view}{$edit}{$delete}");
        }

        $articleList->addElement($tbody);
        return $articleList;
        //Artykuły
        // tytuł, treść (ograniczona) - teaser albo wcale, opcje -> edytuj usuń, wyświetl
    }


    public function getArticleForm(string $action = 'create') : HtmlTag
    {
        //validate action
        if (!in_array($action, ['create', 'edit'])) {
            //throw albo
            $action = 'create'; //wyciszenie
        }

        $submitText = ucfirst($action) . ' article';
        $form = HtmlTag::createElement('div');

        $titleInput = HtmlTag::createElement('input');
        $titleInput->addClass('form-control')
            ->set('id', 'title')
            ->set('type', 'text')
            ->set('name', 'title');

        $contentText = HtmlTag::createElement('textarea');
        $contentText->addClass('form-control')
            ->set('id', 'content')
            ->set('name', 'content')
            ->set('rows', '10')
            ->set('cols', '30');

        if ($action == 'edit') {
            $article = reset($this->data);
            $this->validateArticle($article);
            $titleInput->set('value', $article->getTitle());
            $contentText->text($article->getContent());
        } else {
            $contentText->set('placeholder', 'Article content...');
        }

        $form->set('id', $action . '-article')
            ->addElement('div')
            ->set('id', 'id') //TODO should be taken from url but i have no time for that now
            ->set('style', 'display:none;')
            ->text(!empty($article) ? $article->getId() : '')
            ->getParent()
            ->addElement('div')
            ->addClass('form-group')
            ->addElement('label')
            ->set('for', 'title')
            ->text('Title')
            //back to div
            ->getParent()
            ->addElement($titleInput)
            //back to form
            ->getParent()->getParent()
            ->addElement('div')
            ->addClass('form-group')
            ->addElement('label')
            ->set('for', 'content')
            ->text('Content')
            //back to div
            ->getParent()
            ->addElement($contentText)
            //back to form
            ->getParent()->getParent()
            ->addElement('button')
            ->set('id', $action . 'Btn')
            ->set('onclick', $action . 'Article()')
            ->addClass('btn')
            ->addClass('btn-primary')
//            ->set('type', 'submit')
            ->text($submitText);;

        return $form;
    }
}