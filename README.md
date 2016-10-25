![alt tag](http://www.hdivsecurity.com/img/cip-logo.jpg) 

This work has been developed in the context of the SWEPT project partially funded by the European Community's Competitiveness and Innovation Framework Programme (CIP/2007-2013 http://ec.europa.eu/cip/) under grant agreement n° 621056

# Hdiv for Symfony Reference Documentation

1. Introduction
2. State of the art
3. Hdiv
4. Requeriments
5. Installation
6. Configuration
7. References

## 1. Introduction

Nowadays, web application security is one of the most important issues in the information system development process. According to Gartner [1] the 75% of the attacks performed nowadays are aimed to web applications, because operative system security and net level security have increased considerably. As a result, it is considered that the 95% of the web applications are vulnerable to a certain type of attack [2]. In the following chart we can see the list of the most important vulnerabilities published by OWASP (Open Web Application Security Project) [3]:

![OWASP Top 10](http://www.hdivsecurity.com/img/github/wiki/chapter1/owasptopten.png)

In the following chapters four of the most important vulnerability types are described in detail: Parameter Tampering, SQL Injection, Cross-site Scripting (XSS) and Cross-site Request Forgery (CSRF).

### 1.1 Parameter tampering
Parameter tampering is a type of attack based on the modification of the data sent by the server in the client side.
The process of data modification is very simple for the user. When a user sends a HTTP request (GET or POST), the received HTML page may contain hidden values, which can not be seen by the browser but are sent to the server when a submit of the page is committed. Also, when the values of a form are "pre-selected" (drop-down lists, radio buttons, etc.) these values can be manipulated by the user and thus the user can send an HTTP request containing the parameter values he wants.
Example: We have a web application of a bank, where its clients can check their accounts information by typing this url (XX= _account number_):

![mybank.com](http://www.hdivsecurity.com/img/github/wiki/chapter1/mybankcom.png)

When a client logs in, the application creates a link of this type for each account of this client. So, by clicking in the links, the client can only access to its accounts. However, it would be very easy for this user to access another user account, by typing directly in a browser the bank url with the desired account number.
For this reason the application (server side) must verify that the user has access to the account he asks for.
The same occurs with the rest of non editable html elements that exist in web applications, such as, selectionable lists, hidden fields, checkboxes, radio buttons, destiny pages, etc.
This vulnerability is based on the lack of any verification in the server side about the created data and it must be kept in mind by the programmers when they are developing a new web application.
Despite being a link the modified element in this example, we must not forget that it is possible to modify any type of element in a web page (selects, hidden fields, radio buttons...). This vulnerability does not only affect to GET requests (links) because POST request (forms) can also be modificated using appropriate audit tools [4], which are very easy to use by anyone who knows how to use a web browser.

### 1.2 SQL Injection
In this case the problem is based in a bad programming of the data access layer.
Example: We have a web page that requires user identification. The user must fill in a form with its username and password. This information is sent to the server to check if it is correct:

![SQL Injection](http://www.hdivsecurity.com/img/github/wiki/chapter1/sqlInjection-1.png)

As we can see in the example, the executed sql is formed by concatenating directly the values typed by the user.

In a normal request where the expected values are sent the sql works correctly. But we can have a security problem if the sent values are the following ones:

![SQL Injection](http://www.hdivsecurity.com/img/github/wiki/chapter1/sqlInjection-2.png)

In this case, the generated sql returns all the users of the table, without having typed any valid combination of username and password. As a result, if the program doesn’t control the number of returned results, it might gain access to the private zone of the application without having permission for that.

The consequences of the exploitation of this vulnerability can be mitigated by limiting the database permissions of the user used by the application. For example, if the application user can delete rows in the table the consequences can be very severe.

### 1.3 Cross-Site Scripting (XSS)
This attack technique is based in the injection of code (javascript or html) in the pages visualized by the application user.

**Example:** We have a web page where we can type a text, as is shown in the image below:

![XSS](http://www.hdivsecurity.com/img/github/wiki/chapter1/xss.png)

The html code of the page is:

![XSS HTML code](http://www.hdivsecurity.com/img/github/wiki/chapter1/xss-html-code.png)

Typing the following text in the textbox:

![XSS script](http://www.hdivsecurity.com/img/github/wiki/chapter1/xss-script.png)

This is the result:

![XSS Vulnerability Example](http://www.hdivsecurity.com/img/github/wiki/chapter1/xss-vulnerability-sample.png)

What can an attacker get when our application is vulnerable to a XSS?

There is a large variety of attacks to exploit this vulnerability. A well known attack is a massive email sending that we see in the picture below, attaching a trusted url (in this example, happy banking) where the final result is the execution of a JavaScript function that can redirect us to another website (a fake website which apparently is the same as original) or can obtain the cookies of our browser and send them to the attacker.

![XSS Mail attack](http://www.hdivsecurity.com/img/github/wiki/chapter1/xss-mail-attack.png)

The rob of cookies can give the attacker access to the web applications where the user is authenticated in that moment (online bank, personal email account, etc.). This is because most of the web applications use cookies to maintain sessions. When the server authenticates a user, it creates an identifier that is stored in the user browser as a cookie. In the successive requests, this identifier is used to identify the user, avoiding having to type the username and password for each request. All this process is managed automatically by the browser itself.

By default Symfony's default teplate engine TWIG, escapes a string for safe insertion into the final output.

### 1.4 Cross-Site Request Forgery (CSRF)
Cross-site request forgery, also known as one click attack or session riding and abbreviated as CSRF (Sea-Surf) or XSRF, is a type of malicious exploit of websites. Although this type of attack has similarities to cross-site scripting (XSS), cross-site scripting requires the attacker to inject unauthorized code into a website, while cross-site request forgery merely transmits unauthorized commands from a user the website trusts.

The attack works by including a link or script in a page that accesses a site to which the user is known (or is supposed) to have authenticated.

**Example:** One user, Bob, might be browsing a chat forum where another user, Mallory, has posted a message. Suppose that Mallory has crafted an HTML image element that references a script on Bob's bank's website (rather than an image file), e.g.,

![CSRF](http://www.hdivsecurity.com/img/github/wiki/chapter1/csrf.png)

If Bob's bank keeps his authentication information in a cookie, and if the cookie hasn't expired, then Bob's browser's attempt to load the image will submit the withdrawal form with his cookie, thus authorizing a transaction without Bob's approval.

A cross-site request forgery is a confused deputy attack against a Web browser. The deputy in the bank example is Bob's Web browser which is confused into misusing Bob's authority at Mallory's direction.

The following characteristics are common to CSRF:

* Involve sites that rely on a user's identity
* Exploit the site's trust in that identity
* Trick the user's browser into sending HTTP requests to a target site
* Involve HTTP requests that have side effects

At risk are web applications that perform actions based on input from trusted and authenticated users without requiring the user to authorize the specific action. A user that is authenticated by a cookie saved in his web browser could unknowingly send an HTTP request to a site that trusts him and thereby cause an unwanted action.

CSRF attacks using images are often made from Internet forums, where users are allowed to post images but not JavaScript.
#### 1.4.1 Effects
This attack relies on a few assumptions:

* The attacker has knowledge of sites the victim has current authentication on (more common on web forums, where this attack is most common)
* The attacker's "target site" has persistent authentication cookies, or the victim has a current session cookie with the target site
* The "target site" doesn't have secondary authentication for actions (such as form tokens)

While having potential for harm, the effect is mitigated by the attacker's need to "know his audience" such that he attacks a small familiar community of victims, or a more common "target site" has poorly implemented authentication systems (for instance, if a common book reseller offers 'instant' purchases without re-authentication).

#### 1.4.2 Protection
Applications must ensure that they are not relying on credentials or tokens that are automatically submitted by browsers. The only solution is to use a custom token that the browser will not ‘remember’ and then automatically include with a CSRF attack.

The following strategies should be inherent in all web applications:

* Ensure that there are no XSS vulnerabilities in your application.
* Insert custom random tokens into every form and URL that will not be automatically submitted by the browser. For example,

    ![CSRF protection](http://www.hdivsecurity.com/img/github/wiki/chapter1/csrf-protection.png)

    and then verify that the submitted token is correct for the current user. Such tokens can be unique to that particular function or page for that user, or simply unique to the overall session. The more focused the token is to a particular function and/or particular set of data, the stronger the protection will be, but the more complicated it will be to construct and maintain.

* For sensitive data or value transactions, re-authenticate or use transaction signing to ensure that the request is genuine. Set up external mechanisms such as e-mail or phone contact in order to verify requests or notify the user of the request.

* Do not use GET requests (URLs) for sensitive data or to perform value transactions. Use only POST methods when processing sensitive data from the user. However, the URL may contain the random token as this creates a unique URL, which makes CSRF almost impossible to perform.

* POST alone is insufficient a protection. You must also combine it with random tokens, out of band authentication or re-authentication to properly protect against CSRF.

While these suggestions will diminish your exposure dramatically, advanced CSRF attacks can bypass many of these restrictions. The strongest technique is the use of unique tokens, and eliminating all XSS vulnerabilities in your application.

It should be noted that **preventing CSRF requires that all XSS problems are removed first**. An XSS flaw can be used to retrieve the form, then grab the random tokens before submitting the CSRF request. XSS may also be able to spoof the user into entering their credentials, which would allow the CSRF to bypass re-authentication as well.

CSRF has been called the "sleeping giant" of web application security flaws, because it has yet to be exploited widely. It is only a matter of time, web programmers should be making the changes needed to ensure that their sites are not vulnerable.

## 2. State of the Art
All the vulnerabilities presented before can be solved through a proper input validation. There are solutions for this but most of them are custom solutions and developers have to create a new solution for each use case. Also we must add that it’s highly probable that developers forget a validation in some points of the web application.

In order to solve this problem there are some global solutions. Web application framework validators can be useful to solve problems like _SQL Injection_ or _XSS_ but it’s limited to type validation. **We can’t solve parameter tampering through Symfony’s framework.**

With these validators we can assure that a parameter it’s an integer but we can't know if the value it’s the same that the server sent to the client. In other words, we can’t assure server data integrity. Avoiding this vulnerability manually implies a great development effort and it is likely to fail in some pages because it is very difficult to test the correct programming of each page.


## 3. Hdiv
### 3.1 Introduction
In order to solve web application vulnerabilities in Symfony,  **Hdiv for Symfony project** has been created.

We can briefly define Hdiv as a **Java Web Application Security Framework**. Hdiv extends web applications’ behaviour by adding Security functionalities, maintaining the API and the framework specification. This implies that we can use Hdiv in applications developed in Symfony in a **transparent way to the programmer** and without adding any complexity to the application development. 

The **security functionalities added to the web applications** are these:

* **Integrity:** Hdiv guarantees integrity (no data modification) of all the data generated by the server which should not be modified by the client (links, hidden fields, combo values, radio buttons, destiny pages, cookies, headers, etc.). Thanks to this property we avoid all the vulnerabilities based on the parameter tampering.

* **Editable data validation:** Hdiv eliminates to a large extent the risk originated by attacks of type _Cross-site scripting (XSS)_ and _SQL Injection_ using generic validations of the editable data (text and textarea).

    As there isn't any base in editable data to validate the information, the user will have to configurate generic validations through rules in XML format, reducing or eliminating the risk against attacks based on the defined restrictions.

    Unlike the traditional solution where validations are applied to each field, and where the probability of a human error is very high, Hdiv allows to apply generic rules that avoid to a large extent the risk within these data types. Anyway, it is advisable to use existing solutions to avoid _Cross-site scripting (XSS)_ attacks and to use prepared statements to avoid _SQL injection_ in the data access layer.

    The responsability of showing error messages on the user screen, if the Hdiv validator detects not allowed values in editable fields, is delegated to the errors handler and this handler will show them in the input form.

* **Anti-CSRF token:** Random string (_HDIV_STATE_) called a token is placed in each form and link of the HTML response, ensuring that this value will be submitted with the next request. This random string provides protection because not only does the compromised site need to know the URL of the target site and a valid request format for the target site, it also must know the random string which changes for each visited page.

Therefore, Hdiv **helps to eliminate** most of the web vulnerabilities **based on non editable data** and **it can also avoid vulnerabilities related with editable data** through generic validations.

### 3.2 Base concepts
Before detailing the way Hdiv guarantees data integrity and confidentiality it is necessary to explain some base concepts.
#### 3.2.1 State
For Hdiv a State represents all the data that composes a possible request to a web application, that is, the parameters of a request, its values and its types and the destiny or page request.

For example, having this type of link, http://www.host.com/page1.do?data1=20&data2=35 a state that represents this link is as follows:

![State](https://hdivsecurity.com/img/github/wiki/chapter3/state.png)

We may have more than one state (possible request) for a page which represents the links and forms existing in the page. When a page (TWIG) is processed in the server, Hdiv generates an object of type state for each existing link o form in the page (TWIG).

Generated state can be stored in two locations:

* **Server:** States are stored inside de session (HttpSession) of the user.
* **Client:** State objects are sent to the client as parameters. For each possible request (link or form) an object that represents the state of the request is added.

These states make it possible the later verification of the requests sent by the clients, comparing the data sent by the client with the state.

![State](http://www.hdivsecurity.com/img/github/wiki/chapter3/hdiv-validation.png)

By default, the name of the parameter that contains the Hdiv state included in all the requests is _HDIV_STATE_ but it is possible to configure it in the randomName bean to get a random name for each session user, which decreases the risk of suffering a Cross Site Request Forgery (CSRF) attack.


## 4. Requeriments

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


## 5. Installation

The disruptive approach of HDIV technology allows it to be seamlessly integrated with the foundational software used to develop applications. Thus, using HDIV will not require updating the programming environment, as HDIV will be installed as a library within the web app under development, requiring only a declarative configuration based on XML files. 

In consequence, HDIV technology avoids the traditional integration process of current WAF security tools in production environments. It does not add complexity for programmers who do not need to be security experts to apply HDIV security mechanisms during the web application development process. 

Once HDIV has been integrated within the web application process, it provides completely automatic functionalities allowing developers to ensure security in their applications. 

The source code of the web applications protected by HDIV mechanisms does not change due to the use (or not) of HDIV technology. For this reason, HDIV can be applied to already developed web applications and to those under development. 

Hdiv library for Symfony extends Symfony classes in order to process the links and forms as we have seen in the previous chapters. In the following image you can see how Hdiv works in the request flow:

![alt tag](http://www.hdivsecurity.com/img/symfony.png) 

When a request is made, Hdiv Validator filter decides if it is a valid request or not. If it is not a valid request, Hdiv rejects that request, otherwise Hdiv allows it.

As you can see in the following image, when you want to deploy a PHP web application, you need to deploy it in an Apache Server through different folders. Hdiv library is located inside the web applications' folder like the other libraries.
![alt tag](http://www.hdivsecurity.com/img/apache.png) 

The thrid parties libraries like Hdiv, they have to be located inside vendor folder. Here it is an usual Symfony folder/application structure:

- app/ 
    The application configuration, templates and translations.
- bin/
    Executable files (e.g. bin/console).
- src/
    The project's PHP code.
- tests/
    Automatic tests (e.g. Unit tests).
- var/
    Generated files (cache, logs, etc.).
- vendor/
    The third-party dependencies.
- web/
    The web root directory.

This makes it very easy to deploy Hdiv in any environment. 

These are the instructions to install Hdiv in a Symfony web application:

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
        <startPage>@^/hdiv-symfony-showcase/web/app_dev\.php/$@</startPage>
        <startPage>@^/hdiv-symfony-showcase/web/$@</startPage>
        
        <!--
          Don't change if you are using wdt and profiler default paths
           -->
        <startPage>@^/hdiv-symfony-showcase/web/app_dev\.php/_profiler/@</startPage>
        <startPage>@^/hdiv-symfony-showcase/web/app_dev\.php/_wdt/@</startPage>

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
        <validationRule url="@^/hdiv-symfony-showcase/web/app_dev\.php/@" enableDefaultBlackListRules="true"></validationRule>
        <validationRule url="@^/hdiv-symfony-showcase/web/app_dev\.php/dashboard/$@" enableDefaultBlackListRules="false">extraSafe</validationRule>

        <!-- prod urls -->
        <validationRule url="@^/hdiv-symfony-showcase/web/@" enableDefaultBlackListRules="true"></validationRule>
        <validationRule url="@^/hdiv-symfony-showcase/web/dashboard/$@" enableDefaultBlackListRules="false">extraSafe</validationRule>
    </editableValidations>

</hdiv-config>


```
## 6. Configuration
### 6.1 StartPages
By default the Hdiv does not allow any request that has not been registered previously. Of course, when a user accesses for the first time, it is not possible to register the resource (url) requested by the user. In this case Hdiv uses the concept of start pages - URLs to which Hdiv makes no validation. We can see start pages as the home pages of your website.
Note that Hdiv is usually applied only to authenticated parts of the web page.
This example shows the configuration code to define a start page to access the home page of web site.

```
        <startPage>@^/hdiv-symfony-showcase/web/app_dev\.php/$@</startPage>
```
### 6.2 Editable data validation
As explained above, Hdiv offers a generic validation feature, applying validation rules to all editable data sent by the client. This includes information that comes from web form fields such as textbox and textarea.

Hdiv includes a default group of validation rules that try to avoid the most common risks such as XSS and SQL Injection.
In order to activate Hdiv editable validation with the default configuration, it is necessary to add the code below to your Symfony configuration:

```
<editableValidations enabled="true">
</editableValidations>
```
In addition to the default validations it is possible to create your own custom validations. To do this, first it is necessary to create a validation entity, which could contain two kinds of patterns:

- **AcceptedPattern**: the parameter value must match the pattern (whitelist), otherwise Hdiv generates an error message that is visible within the original form.
- **RejectedPattern**: if the parameter value matches the defined pattern (blacklist), Hdiv generates an error that is visible within the original form or the Hdiv generic error page.

In order to create a validation it is necessary to create a validation entity within Hdiv config file:

```
    <validations>
        <validation name="extraSafe">
            <acceptedPattern><![CDATA[/^[a-z0-9 .\-]+$/i]]></acceptedPattern>
        </validation>
    </validations>
 ```   
Once validation has been defined, it is necessary to make an additional step to activate it. To do this, add a validation rule to the editableValidations entity. The validation rule is applied to a specific url pattern. We see here an example to apply the validation as defined above:

```
    <editableValidations enabled="true">
      <validationRule url="@^/hdiv-symfony-showcase/web/dashboard/$@" enableDefaultBlackListRules="false">extraSafe</validationRule>
    </editableValidations>
```

All editable fields of the request are validated by default. 

### 6.3 Excluded extensions
In addition to start pages configuration and the url mapping defined by Hdiv, Hdiv allows validation exclusion of some extensions. Equally, it is possible to include other extensions.

```
    <excludedExtensions>
        <excludedExtension>.css</excludedExtension>
    </excludedExtensions>
```
### 6.4 Memory cache
When Hdiv is executing, the amount of memory stored within the Session should be limited. By default, the cache size is 3 so Hdiv stores all the data related to the last 3 pages visited by the user. This means that if the user accesses one of the previous 3 pages, and uses a link or button, it is a valid request for Hdiv.

If it is necessary to reduce the memory consumption to improve the app scalability, it is good practice to reduce the default value. But if you need to increase back button support to more than 3 pages, increase the default value.

It is possible to overwrite this attribute within Hdiv config:

```
    <maxPagesPerSession>3</maxPagesPerSession>
```

### 6.5 Debug mode
A debug execution mode is offered in order to apply Hdiv in production environments without any functional or integration problems. In other words Hdiv processes and validates all the requests but does not change the original request execution.

It is possible to configure this option from Hdiv configuration:

```
    <debugMode>False</debugMode>
```

### 6.6 Random name

_HDIV_STATE_ is the default name of the Hdiv parameter.

There is a configuration option to enable the subtitution of the constant parameter name with a random name. In this case a per user random string is generated and used as the parameter name.

It is possible to configure this option from Hdiv configuration:

```
    <randomName>True</randomName>
```

### 6.7 Logger

Hdiv has a logger that will print in a file all the attacks detected, which helps system administrators checking the attacks the web application has suffered.
Hdiv provides an example property file (hdivloggerconfig.properties) that has to be placed on App/Resources. If the file does not exist, a default logger will be use:

```
Hdiv Security;[type of attack];[url];[Ip];[fieldName];[fieldType];[fieldValue];[ruleName]
```

[type of attack]: Type of attack detected by Hdiv

[url]: Url or action name the HTTP request was directed to.

[IP]: IP address the request was made from.

[fieldName]: Form field name.

[fieldValue]: Form field value.

[fieldType]: Form field type value.

[ruleName]: Editable rule name.


## 7. References
[1]. Gartner, Nov 2005

http://gartner.com

[2]. Studies from numerous penetration tests by Imperva

http://www.imperva.com/application_defense_center/papers/how_safe_is_it.html

[3]. Open Web Application Security Project

http://www.owasp.org/

[4]. Examples of Basic tools of web audit:

* For Firefox (Tamper Data): https://addons.mozilla.org/firefox/966/
* For Explorer: (TamperIE): http://www.bayden.com/Other/


