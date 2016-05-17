<img src="http://www.hdivsecurity.com/img/cip-logo.jpg" alt="CIP-logo" title="CIP-logo" align="right" />

This work has been developed in the context of the SWEPT project partially funded by the European Community's Competitiveness and Innovation Framework Programme (CIP/2007-2013 http://ec.europa.eu/cip/) under grant agreement n° 621056
<br/><br/><br/><br/><br/>

-----
<br/>

HDIV for Symfony
=====================

HDIV is an open-source web application security framework that eliminates or mitigates web security risks by design for some of the most used web frameworks. Unlike traditional external web application firewalls, HDIV works within web applications.

Requirements
=======

- Symfony 2.3+
- Always set actions to forms:

```
$form = $this->createFormBuilder()->setAction($this->generateUrl('app_check_post'));
```

- Use Twig render form:

```
{# app/Resources/views/Default/new.html.twig #}
{{ form_start(form) }}
{{ form_widget(form) }}
{{ form_end(form) }}
```


Installation
=======


1.Add the following code to your composer.json file:

```
"require": {
      ...
        "hdiv/securitybundle": "dev-master"
      ...
},
"repositories" : [{
        "type" : "vcs",
        "url" : "https://github.com/hdiv/HDIVSecurityBundle.git"
}],
```

2.Update and install your dependencies:

```
$ composer update
```

```
$ composer install
```

3.Enable the bundle in the kernel (app/appKernel.php):

```
public function registerBundles()
{
    $bundles = array(
        // ...
        new HDIV\SecurityBundle\HDIVSecurityBundle(),
    );
}
```

4.Add HDIVSecurityBundle’s routing (app/config/routing.yml):

```
hdiv:
    resource: "@HDIVSecurityBundle/Resources/config/routing.yml"
    prefix:   /
```

5.Create 'hdivconfig.xml' on app/Resources/hdivconfig.xml

```

<!--
    REPLACE 'hdiv-symfony-showcase' with your app !!!
-->

<?xml version="1.0" encoding="UTF-8"?>
<hdiv-config>

    <startPages>

        <!--
         Your entrance to your application
           -->
        <startPage>@^/hdiv-symfony-showcase/web/app_dev.php/$@</startPage>
        <startPage>@^/hdiv-symfony-showcase/web/$@</startPage>
        
        <!--
          Don't change if you are using wdt and profiler default paths
           -->
        <startPage>@^/hdiv-symfony-showcase/web/app_dev.php/_profiler/@</startPage>
        <startPage>@^/hdiv-symfony-showcase/web/app_dev.php/_wdt/@</startPage>

    </startPages>

    <maxPagesPerSession>3</maxPagesPerSession>

    <debugMode>False</debugMode>

    <excludedExtensions>
        <excludedExtension>.css</excludedExtension>
        <excludedExtension>.png</excludedExtension>
        <excludedExtension>.ico</excludedExtension>
        <excludedExtension>.js</excludedExtension>
        <excludedExtension>.jpg</excludedExtension>
    </excludedExtensions>

    <!--
      Don't change if you are using wdt and profiler default paths
       -->
    <excludedPages>
        <excludedPage>@^/hdiv-symfony-showcase/web/app_dev.php/_profiler/@</excludedPage>
        <excludedPage>@^/hdiv-symfony-showcase/web/app_dev.php/_wdt/@</excludedPage>
    </excludedPages>

    <!--
    Editable validations
    HDIV offers a generic validation functionality that makes possible the application of validation
    rules to all editable data (information that comes from web forms fields such as text and password) sent by the client.
    -->

    <!--
    In addition to the default validations it is possible to create your own custom validations. To do this, first it is necessary to create a validation entity,
    which could contain two kinds of patterns:

        AcceptedPattern: the parameter value must match the pattern (whitelist), otherwise Hdiv generates an error message that is visible within the original form.
        RejectedPattern: if the parameter value matches the defined pattern (blacklist), Hdiv generates an error that is visible within the original form.
    -->
    <validations>
        <validation name="extraSafe">
            <acceptedPattern><![CDATA[/^[a-z0-9 .\-]+$/i]]></acceptedPattern>
        </validation>
    </validations>

    <!--
    Once validation has been defined, it is necessary to make an additional step to activate it. To do this, add a validation rule to the editableValidations entity.
    The validation rule is applied to a specific POST/action url pattern. We see here an example to apply the validation as defined above:
    -->
    <editableValidations enabled="true">
        <!-- dev urls -->
        <validationRule url="@^/hdiv-symfony-showcase/web/app_dev.php/@" enableDefaultBlackListRules="true"></validationRule>
        <validationRule url="@^/hdiv-symfony-showcase/web/app_dev.php/dashboard/$@" enableDefaultBlackListRules="false">extraSafe</validationRule>

        <!-- prod urls -->
        <validationRule url="@^/hdiv-symfony-showcase/web/@" enableDefaultBlackListRules="true"></validationRule>
        <validationRule url="@^/hdiv-symfony-showcase/web/dashboard/$@" enableDefaultBlackListRules="false">extraSafe</validationRule>
    </editableValidations>

</hdiv-config>


```

