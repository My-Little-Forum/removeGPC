   [%InstErrors%]
   <section>
    <p>Enter the credentials for the script login and for the database connection. Please use <strong>the same database</strong> and <strong>the same database credentials</strong> as for your installation of <strong>My Little Forum 1.7.x</strong>.</p>
    <form action="" method="post">
     <fieldset>
      <legend>User login data for the script removeGPC</legend>
      <ul>
       <li><label for="usr-name">app login name</label>
        <input id="usr-name" name="usr_name" type="text" value="[%InstUserName%]" placeholder="Your Login Name"></li>
       <li><label for="usr-pass">app login passowrd</label>
        <input id="usr-pass" name="usr_pass" type="password" value="[%InstUserPass%]"><button type="button" id="passWordButton1" data-for="usr-pass">show password</button></li>
      </ul>
     </fieldset>
     <fieldset>
      <legend>Database connection data</legend>
      <ul>
       <li><label for="db-server">database server name</label>
        <input id="db-server" name="db_server" type="text" value="[%InstDBServer%]" placeholder="localhost"></li>
       <li><label for="db-name">database name</label>
        <input id="db-name" name="db_name" type="text" value="[%InstDBName%]" placeholder="db12345"></li>
       <li><label for="db-user">database user name</label>
        <input id="db-user" name="db_user" type="text" value="[%InstDBUser%]" placeholder="User_Name"></li>
       <li><label for="db-pass">database password</label>
        <input id="db-pass" name="db_pass" type="password" value="[%InstDBPass%]"><button type="button" id="passWordButton2" data-for="db-pass">show password</button></li>
      </ul>
     </fieldset>
     <fieldset>
      <legend>Layout for data output</legend>
      <ul>
       <li><label for="op-entries-per-page">entries per page</label>
        <input id="op-entries-per-page" name="op_entries_per_page" type="number" value="[%InstEntriesPage%]" min="10" max="50" step="5"></li>
      </ul>
     </fieldset>
     <p><button name="send_step1">Send data</button></p>
    </form>
   </section>
