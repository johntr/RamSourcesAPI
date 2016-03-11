<?php

$html =  <<<'EOT'
  <!DOCTYPE html>
  <html>
  <head lang="en">
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css" integrity="sha384-1q8mTJOASx8j1Au+a5WDVnPi2lkFfwwEAa8hDDdjZlpLegxhjVME1fgjWPGmkzs7" crossorigin="anonymous">

    <style>
      .login-form {
        background: #283746;
        padding: 20px;
      }
      .wrapper {
        background: #116F62;
        padding: 10px;
      }
      .form-group input {
        background-color: #D7D7D7;
      }
    </style>
  </head>
  <body class="wrapper">
  <div class="login-form container">
    <div id="responce-text" class="alert alert-dismissible" role="alert"></div>
    <form>
      <div class="form-group">
        <label for="RamEmail">Email address</label>
        <input type="email" class="form-control" id="RamEmail" placeholder="Email">
      </div>
      <div class="form-group">
        <label for="RamPassword">Password</label>
        <input type="password" class="form-control" id="RamPassword" placeholder="Password">
      </div>
      <button type="submit" class="btn btn-default">Submit</button>
    </form>
  </div>
  </body>
  </html>
EOT;
