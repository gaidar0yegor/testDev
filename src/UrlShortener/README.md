# UrlShortener

Permet de générer des urls courtes (comme bit.ly)
qui peuvent être utilisées par exemple dans les SMS pour réduire leurs tailles et donc leurs coût.

## Usage

Pour raccourcir une URL depuis Twig :

``` twig
{{ path('...')|shortenUrl }}
{# Sortie : https://app.rdimanager.com/l/d9z-2s_s #}
```

Pour définir une taille plus grande ou plus petite de l'url courte :

``` twig
{{ path('...')|shortenUrl(18) }}
{# Sortie : https://app.rdimanager.com/l/d9z-2s_sd4xk2slsfd #}
```

Pour raccourcir un lien depuis PHP :

``` php
class Service
{
    private UrlShortener $urlShortener;

    public function method()
    {
        $this->urlShortener->createShortUrl('url ou path');
        $this->urlShortener->createShortUrl('url ou path', 18);
    }
}
```
