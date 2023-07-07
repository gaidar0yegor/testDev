import $ from 'jquery';
import statsMatomo from './bo-dashboard/matomo-api';

const initMatomo = (config) => {
    (function () {
        var _paq = window._paq = window._paq || [];
        _paq.push(['trackPageView']);
        _paq.push(['enableLinkTracking']);
        (function() {
            var u=config.host;
            _paq.push(['setTrackerUrl', u+'matomo.php']);
            _paq.push(['setSiteId', config.siteId]);
            var d=document, g=d.createElement('script'), s=d.getElementsByTagName('script')[0];
            g.type='text/javascript'; g.async=true; g.src=u+'matomo.js'; s.parentNode.insertBefore(g,s);
        })();
    })();

    const pushEvent = (category, action, name, value) => {
        const _paq = window._paq || [];
        _paq.push([ 'trackEvent', category, action, name, value]);
    };

    // Push menu events
    $('.navbar .nav-link:not(".dropdown-toggle")').on('click', function (e) {
        const menuName = $(this).text().trim();

        pushEvent('Menu', 'Navigate', menuName);
    });

    // Push submenu events
    $('.navbar .dropdown-item').on('click', function () {
        const $subMenu = $(this);
        const $menu = $subMenu.closest('.nav-item').find('.nav-link');

        const menuName = [
                $menu.text().trim(),
                $subMenu.text().trim(),
            ]
                .filter(notEmpty => notEmpty)
                .join(' > ')
        ;

        pushEvent('Menu', 'Navigate', menuName);
    });

    window.pushEvent = pushEvent;
    statsMatomo(config);
};

global.initMatomo = initMatomo;
