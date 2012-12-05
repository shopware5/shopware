=======================
Shopware JIRA Component
=======================

Das nachfolgende Dokument beschreibt die wesentlichen Aspekte für die Benutzung
der JIRA-Komponente.

Systemvoraussetzungen
=====================

PHP
---

- Version >= 5.3

- ext/pdo

- ext/curl

- ext/phar

 - Phar Support in Suhosin (/etc/php5/conf.d/suhosin.ini)

   - in der \*.ini Datei die Option "suhosin.executor.include.whitelist" auf
     "phar" setzen

     suhosin.executor.include.whitelist = phar

Libraries
---------

Für die Kommunikation mit der JIRA-REST-API wurde die PHP-Library Guzzle[1]
verwendet. Die gesamte Bibliothek ist bereits im Subversion eingecheckt und
kann im Verzeichnis /Vendor gefunden werden.

Verzeichnisstruktur
===================

Der Quelltext der JIRA-Komponent ist im Branch "sw_trac" unter dem Verzeichnis
"engine/Shopware/Components/Jira" zu finden. In diesem Verzeichnis sind die
folgenden Unterordner zu finden.

- API: Dieses Verzeichnis enthält die eigentlich JIRA-API-Spezifikation, in Form
  von Service-Schnittstellen und einer Reihe von Model-Klassen. Mit Hilfe dieser
  Schicht wird das eigentlich Issue-Tracking-System vollständig abstrahiert, so
  dass bei einem Wechsel von JIRA zu einer anderen Lösung einfach eine neue
  Implementierung für das neue System erstellt werden kann, ohne den nutzenden
  Code zu ändern.

- SPI: Dieses Verzeichnis enthält so genannte Service-Provider-Interfaces, die
  die konkreten Datenzugriffsschichten (REST und MySQL) und Datenstrukturen
  abstrahieren. Diese Schicht stellt sicher, dass auch zu einem späteren
  Zeitpunkt einfach auf eine 100% REST-basierte, MySQL-basierte oder sonstige
  Technologie gewechselt werden kann, ohne den restlichen Applikations Code
  ändern zu müssen.

- Vendor: In diesem Verzeichnis ist die verwendete HTTP-Bibliothek zu finden.

- Core: In diesem Verzeichnis sind die eigentlichen Implementierungen der
  Schnittstellen-Spezifikationen aus "API" und "SPI" zu finden.

- UseCases: Dieses Verzeichnis enthält eine Reihe von Use-Cases, die die
  Verwendung der gesamten API in einem rudimentären HTML-Prototypen
  demonstrieren.

Bootstrap
=========

Da die JIRA-Komponente losgelöst von dem umgebenden Shopware-Framework entwickelt
wurde, ist im Hauptverzeichnis der Komponente eine Datei "bootstrap.php" zu
finden, in der der Autoloader für die JIRA-Komponente und die Bibliothek
Guzzle[1] definiert wird. Bei einer späteren Integration in den öffentlichen
Shopware-Issue-Tracker, kann dieser Code durch den entsprechenden Shopware
eigenen Autoloader ersetzt werden.

Verwendung
==========

Die JIRA-Komponente setzt auf eine so genannte Service-basierte Architektur auf,
bei der alle Operation ausschließlich über Service-Objekte erfolgt und die
eigentlichen Domain-Objekte keine eigene Funktionalität bereitstellen und auf
die Eigenschaften ausschließlich lesend zugegriffen wird. Alle Änderungen an
Domain-Objekten geschieht ausschließlich über Methoden die von den Services
bereitgestellt werden.

Um auf die einzelnen Services zuzugreifen stellt die Komponente ein ``Context``
Interface bereit, dass für die Instanzierung von konkreten Service-Implementierungen
verantwortlich ist. Das nachfolgende Beispiel zeigt den Zugriff auf Services
unter Verwendung eines ``Context`` Objekts. ::

  <?php
  /* @var $context \Shopware\Components\Jira\API\Context */

  // Get service for Project read access
  $projectService = $context->getProjectService();

  // Get service for Issue read/write access
  $issueService = $context->getIssueService();

  // Get service for Version read access
  $versionService = $context->getVersionService();

Mit Hilfe dieser Abstraktion stellen wir sicher, dass eine Anwendung die die
JIRA-Komponente verwendet nur die im Namespace ``\Shopware\Components\Jira\API``
definierten Interfaces kennt und von der konkreten Implementierung unabhängig
ist.

Laden Domain-Objekten
---------------------

Dieser Abschnitt beschreibt das laden von einzelnen Domain-Objekten über die
entsprechende Service-Schnittstelle. Als Beispiel dient uns hier das Domain-Objekt
``\Shopware\Components\Jira\API\Project``. ::

  <?php
  /* @var $context \Shopware\Components\Jira\API\Context */

  // Get service for Project read access
  $projectService = $context->getProjectService();

  // Load a single project by it's ID
  $project = $projectService->load(10100);

  // Do something with the project
  echo $project->getName(), PHP_EOL;

Das hier gezeigte Vorgehen ist identisch für alle Domain-Klassen, für die eine
entsprechende Service-Schnittstelle existiert. Die Benennung von Methoden folgt
in allen Services dem gleichen Schema. Eine Methode ``load()`` erwartet immer
eine numerische ID um ein Objekt zu laden. Während häufig auf weitere Methoden
``loadBy...()`` existieren, die Objekte anhand eines anderen OBjekts laden.

- ``ComponentService::loadByProject(Project $project)``

- ``KeywordService::loadByIssue(Issue $issue)``

Erzeugen von Domain-Objekten
----------------------------

Dieser Abschnitt beschreibt das Erzeugen von neuen Domain-Objekten im verwendeten
Issue-Tracker.

Da die JIRA-Komponente ein Readonly Domain-Model verwendet, benötigen wir
zusätzliche Objekte, über die wir die Informationen über das zu erzeugende
Objekt in die Service-Schnittstelle transportieren. Der für die JIRA-Komponente
gewählte Ansatz verwendet hierfür einfach Value Objekte, die es erlauben über
Setter-Methoden die entsprechenden Werte zu setzen. Nachfolgend ein Beispiel
in dem wir ein neues Issue anlegen. ::

  <?php
  /* @var $context \Shopware\Components\Jira\API\Context */

  // Get the issue service
  $issueService = $context->getIssueService();

  // Create a new issue create struct for the context project
  $issueCreate = $issueService->newIssueCreate($project);

  // Set some common properties
  $issueCreate->setType(IssueType::TYPE_BUG);
  $issueCreate->setName('My example bug');
  $issueCreate->setDescription('When I open example.com nothing happens.');
  // Set current remote user...
  $issueCreate->setRemoteUser($context->getCurrentRemoteUser());

  // Now we can create the new issue
  $issue = $issueService->create($issueCreate);

Das Beispiel zeigt sehr gut, wie mit Hilfe eines zusätzlichen ``$issueCreate``
Objekt verhindert wird, dass Eigenschaften des eigentliche ``Issue`` Objekts
öffentlich gemacht werden müssen.

Das hier gezeigte Vorgehen ist identisch mit den erforderlichen Schritten um
einen neuen Kommentar unter einem ``Issue`` anzulegen.

Änderungen an ``Issue`` Objekten werden auf ähnliche Art durchgeführt. ::

  <?php
  /* @var $context \Shopware\Components\Jira\API\Context */

  // Get the issue service
  $issueService = $context->getIssueService();

  // Load issue to edit
  $issue = $issueService->load(10123);

  // Create a new issue update struct
  $issueUpdate = $issueService->newIssueUpdate();

  // Change some properties
  $issueUpdate->setType(IssueType::TYPE_IMPROVEMENT);
  $issueUpdate->setName('My updated example bug');

  // Update issue and get an updated object version
  $issue = $issueService->update($issue, $issueUpdate);

Suchen und Filtern von Issues
-----------------------------

Die JIRA-Komponente bietet ein sehr flexibles Konzept um in den vorhandenen
Issues zu suchen und nur kleine Untermengen anzeigen zu lassen. Hierfür verwendet
die Komponente eine ``Query`` Klasse und weitere ``Criterion`` Klassen um
zusätzliche Filter zu definieren. ::

  <?php
  /* @var $context \Shopware\Components\Jira\API\Context */

  use \Shopware\Components\Jira\API\Model\Query;
  use \Shopware\Components\Jira\API\Model\IssueType;
  use \Shopware\Components\Jira\API\Model\IssueStatus;
  use \Shopware\Components\Jira\API\Model\Query\Criterion\Type;
  use \Shopware\Components\Jira\API\Model\Query\Criterion\Status;

  // Get the issue service
  $issueService = $context->getIssueService();

  // Get the project service
  $projectService = $context->getProjectService();

  // Create a query object
  $query = new Query();

  // Set some default paging information
  $query->setOffset(23); // Will become something like SELECT ... LIMIT (23 * 42), 42
  $query->setLength(42);

  // Configure sorting
  $query->setOrderBy(Query::ORDER_BY_CREATED_AT);
  $query->setOrderDir(Query::ORDER_ASC);

  // We only want Bugs
  $query->addCriterion(new Type(IssueType::TYPE_BUG));

  // We only want unclosed bugs
  $query->addCriterion(
      new Status(
          array(
              IssueStatus::STATUS_OPEN,
              IssueStatus::STATUS_IN_PROGRESS,
              IssueStatus::STATUS_REOPENED,
          )
      )
  );

  // Query all issues that match this query
  $result = $issueService->loadIssues($project, $query);

  // Print total number of available object
  var_dump($result->getTotal());

  // Do something with the issues
  foreach ($result->getIssues() as $issue) {
      echo $issue->getName(), PHP_EOL;
  }

Aufsetzen des Context
---------------------

Um die zuvor gezeigten Use-Cases ausführen zu können, benötigt man ein fertig
konfiguriertes ``Context`` Objekt. Der folgende Quelltext zeigt wie ein Objekt
vom Typ ``Context`` für die aktuelle Implementierung initialisiert wird. ::

  <?php
  use \Shopware\Components\Jira\Core\Rest\Client;
  use \Shopware\Components\Jira\Core\Service\Context;
  use \Shopware\Components\Jira\Core\Mapper\MapperFactory;
  use \Shopware\Components\Jira\Core\Storage\Mixed\GatewayFactory;

  require_once __DIR__ . '/../bootstrap.php';

  // Root url where JIRA could be found
  $jiraUrl = 'http://jira_extern:123a21ex%@jira.shopware.cc:1234/jira';

  // Database credentials
  $dbdsn  = 'mysql:host=localhost;dbname=jira';
  $dbuser = 'dbuser';
  $dbpass = 'dbpass';

  // Unique user identifier for Shopware's single signon
  $remoteUser = 'Shopware-Single-SignOn-User-Key';

  // Initialize the database connection
  $pdo = new \PDO($dbdsn, $dbuser, $dbpass);
  $pdo->query('SET NAMES `utf8`');

  // Initialize the REST client.
  $rest = new Client(
      new \Guzzle\Http\Client($jira, array('version' => 'latest'))
  );

  // Initialize a context implementation
  $context = new Context();
  $context->initialize(
      new MapperFactory($context),
      new GatewayFactory($pdo, $rest),
      $remoteUser
  );

Dieser Quelltext kennt die konkreten Implementierungen und setzt sie zu einer
lauffähigen JIRA-Komponente zusammen. Dieser Code sollte in der geplanten
Anwendung irgendwo ausgelagert werden und in den einzelnen Controllern dann nur
mit ``$context`` vom Type ``\Shopware\Components\Jira\API\Context`` gearbeitet
werden.

Zum obigen Beispiel ist noch anzumerken, dass der dem REST-Client übergebene
Parameter ``array('version' => 'latest')`` die verwendete JIRA-API Version
spezifiziert und gegebenfalls bei neueren JIRA Versionen auf *2* geändert werden
muss. Zum Zeitpunkt der Entwicklung der Komponente musste die Version ``latest``
lauten.

Weitere Beispiele
-----------------

Alle von der Komponente bereitgestellten Methoden und deren Verwendung kann in
den beigefügten Use-Cases eingesehen werden. Hier finden Sie auch Beispiele zur
Verwendung des ``Context`` um Benutzerrechte auf einzelnen Objekte oder
Funktionen abzufragen.



Verweise
========

[1] http://guzzlephp.org/