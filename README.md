Guzzle Profiler Bundle
======================

Provides a basic logger and an advanced profiler for Guzzle

* The basic logger use the default Symfony app logger, it's safe to use in your production environment.
* The advanced profiler is for debug purposes and will display a dedicated report available in the toolbar and Symfony Web Profiler

<img src="http://ludofleury.github.io/GuzzleBundle/images/guzzle-profiler-panel.png" width="280" height="175" alt="Guzzle Symfony web profiler panel"/>
<img src="http://ludofleury.github.io/GuzzleBundle/images/guzzle-request-detail.png" width="280" height="175" alt="Guzzle Symfony web profiler panel - request details"/>
<img src="http://ludofleury.github.io/GuzzleBundle/images/guzzle-response-detail.png" width="280" height="175" alt="Guzzle Symfony web profiler panel - response details"/>

## Installation

Add the composer requirements
```javascript
{
    "require-dev": {
        "ratza/guzzle-profiler-bundle": "dev-master"
    },

    "repositories": {
        {
            "type": "vcs",
            "url": "https://github.com/ratza/guzzle-profiler-bundle.git"
        }
    },
}
```

Add the bundle to your Symfony app kernel
```php
<?php
    // in %your_project%/app/AppKernel.php
    $bundles[] = new Ratza\GuzzleProfilerBundle\RatzaGuzzleProfilerBundle();
?>
```

To enable the advanced profiler & the toolbar/web profiler panel, add this line to your `app/config/config_dev.yml`
```yml
ratza_guzzle:
    web_profiler: true
```

### Add the logger/profiler manually to a Guzzle client

If you need to handle the registration of the logger or profiler plugin manually, you can retrieve theses services from the Symfony container.

```php
<?php

$client = new \GuzzleHttp\Client('https://my.api.com');

// basic logger service plugged & configured with the default Symfony app logger
$loggerPlugin = $container->get('ratza_guzzle.client.logger');
$client->getEmitter()->attach($loggerPlugin);

// advanced profiler for development and debug, requires web_profiler to be enabled
$profilerPlugin = $container->get('ratza_guzzle.client.profiler');
$client->getEmitter()->attach($profilerPlugin);

?>
```

## Customize your own profiler panel

If you need a [custom profiler panel](http://symfony.com/doc/master/cookbook/profiler/data_collector.html) you can extend/reuse easily the data collector and profiler template from this bundle.

For example, you have a GitHubBundle which interact with the GitHub API. You also have a GitHub profiler panel to debug your development and you want to have the API requests profiled in this panel.

It's quite easy:
First, define your own `GitHubDataCollector` extending the `Ratza\GuzzleProfilerBundle\DataCollector\GuzzleDataCollector`


Then extends the guzzle web profiler template
```twig
{% extends 'RatzaGuzzleProfilerBundle:Collector:guzzle.html.twig' %}

{% block panel %}
    <div class="github">
        <h2>GitHub</h2>
        <ul>
            <li><strong>GitHub API key:</strong> {{ collector.getApiKey }}</li>
            <!-- Some custom information -->
        </ul>
    </div>

    {% include 'RatzaGuzzleBundle:Profiler:requests.html.twig' with {'requests': collector.requests } %}
{% endblock %}
```

And finally declare your data collector
```xml
<service id="data_collector.github" class="Acme\GitHubBundle\DataCollector\GitHubDataCollector">
    <argument type="service" id="ratza_guzzle.client.profiler"/>
    <tag name="data_collector"
        template="AcmeGithubBundle:Collector:github"
        id="github"/>
</service>
```

That's it, now your profiler panel displays your custom information and the Guzzle API requests.

## Licence

This bundle is under the MIT license. See the complete license in the bundle

## Credits

* Swagger for the UI
* Ludovic Fleury for the [original](http://ludofleury.github.io/GuzzleBundle/) inspiration. This is a profiler only bundle that uses the same UI, but supports Guzzle Client 4.x and 5.x.


