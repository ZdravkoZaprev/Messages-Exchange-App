<h1>Messages-Exchange-App</h1>
<h2>Setup</h2>
<ol>
  <li>Install RabbitMQ and import rabbitmq_definitions.json</li>
  <li>Make sure RabbitMQ Server runs on http://localhost:15672/</li>
  <li>Install WAMP or XAMPP locally and make sure it runs on http://localhost:8080/ with username root and empty password. If you have different configurations, change it in the .env files for both the main-app and Messages-Exchange-App.</li>
  <li>Make sure you have Symfony CLI installed. For instructions, visit <a href="https://symfony.com/download">https://symfony.com/download</a></li>
  <li>Execute <code>composer install</code> on both the main-app and Messages-Exchange-App.</li>
  <li>Execute <code>symfony console doctrine:database:create</code> followed by <code>symfony console doctrine:migrations:migrate</code> for both apps.</li>
  <li>Make sure there are <code>mainapp</code> and <code>notificationapp</code> databases in your MySQL filled with a few tables.</li>
  <li>Open two terminals (one for the main-app and the other for Messages-Exchange-App) and run <code>symfony serve</code> on both of them. They should be running on:
    <ul>
      <li>Main-app: http://127.0.0.1:8000</li>
      <li>Messages-Exchange-App: http://127.0.0.1:8001</li>
    </ul>
  </li>
  <li>If they are running on different ports, make sure you change <code>main-app\config\services.yaml</code> <code>$apiBaseUri: 'http://localhost:8001/api/'</code> to the URL that runs for Messages-Exchange-App followed by <code>/api/</code>.</li>
  <li>Before hitting any endpoint, run the command <code>symfony console rabbitmq:consume:messages</code> on Messages-Exchange-App.</li>
</ol>
<h2>Notes</h2>
<ul>
  <li>There is also a Messages-Exchange-App API.postman_collection.json collection available for use with Postman.</li>
  <li>Don't forget to run symfony <code>symfony console rabbitmq:consume:messages</code> mentioned in point 10</li>
</ul>
