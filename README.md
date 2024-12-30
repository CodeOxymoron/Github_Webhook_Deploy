# Use Github webhook to deploy website to web server

These directions were written using Ubuntu 22.04 as the server OS and Apache2 as the web server.  
Most Linux systems should be compatible with these setup instructions, but your milage may vary.  

  
### Prereqs
---
###### Setup the server  
There are plenty of tutorials out there on how to setup and secure a web server.  
I am not going to duplicate them here. Web search is your friend. :)

- Make sure Apache2 and php is installed on your web server.
- Make sure you have proper public access your web server and Apache2 is configured to serve your website.
- It would be very wise to configure Apache2 to not serve .git files for security reasons.  
*`RedirectMatch 404 /\.git`*
- Make sure /var/www/sites/ (to be refered to as {web server root} in these instructions)  
(This is the directory I use to host multiple websites from one Apache2 server)


###### Setup a Github repository
1. Create a new Github repository. (I am choosing to make my repository private.)
> If your repository is private, you will need to generate a Github token with permission to access and download repositories.
>> To generate a Github token:
>> 1. Log into Github using a web browser.
>> 2. Click you profile icon in the top right.
>> 3. Click 'Settings'.
>> 4. At the bottom of the left menu, click 'Developer settings'.
>> 5. Expand the 'Personal access tokens' menu and click 'Tokens (classic)'.
>> 6. Generate a new token.
>> 7. Fill in the note with something descriptive to you and make sure to put a check mark in the box next to 'repo'.
>> 8. Click 'Generate token at the bottom and you will be presented with a token key.  
This will be the only time you will be able to see the token. ***Make sure you document it down safely***.

2. Don't push your code to the repository yet. It will make testing easier later.  
If you already have code in the repository it will not cause any problems. You will just need to push a commit instead. :)

  
### Setup
---
#### Our new web root directory setup
---
> Note: You will need to setup your web server config files to host the following directories.  
> This tutorial will not cover setting this up. Web search is your friend. :)  
  
This section will need to be performed each time you initially deploy a new website.
1. Change directory into our web root directory.  
`cd {web server root}`

2. Git clone or download the website files from your repository.  
`git clone https://{username}:{token}@github.com/username/repo.git --depth=1`  
	> When using a private repository with git, the https link needs to be formatted differently.  
	> `https://{username}:{Github personal access token}@github.com/username/repo.git --depth=1`  
	> or  
	> `https://{oauth-key-goes-here}@github.com/username/repo.git --depth=1`

The new folder that just appeared will be refered to as *{your website folder}* for the rest of this tutorial.  
You can change the name if you desire. Just make sure you update the Apache2 configs.  

3. Change ownership of all of the directories and files so the web server user has proper permissions.  
`chown -R www-data:www-data {web server root}/{your website folder}/`  

4. Configure Apache2 to not serve .git files for security reasons.  
Add the line *`RedirectMatch 404 /\.git`* to your websites Apache2 config.


#### Deploy this Github repository
---
1. After you have completed the steps in the *Our new web root directory setup* section. Change directory into the new web root directory.
2. Git clone or download the files from this repository.  
`git clone https://{username}:{token}@github.com/username/repo.git --depth=1`
	> When using a private repository with git, the https link needs to be formatted differently.  
	> `https://{username}:{Github personal access token}@github.com/username/repo.git --depth=1`  
	> or  
	> `https://{oauth-key-goes-here}@github.com/username/repo.git --depth=1`

3. You should now have a directory named *Github_Webhook_Deploy*
4. Now you have to make sure the git update of you website does not erase the Github_Webhook_Deploy folder.
There are two options to do this.
	1. Copy the .gitignore.example file from *{web server root}/{your website folder}/Github_Webhook_Deploy* to *{web server root}/{your website folder}* and rename it to .gitignore
	2. Add the line *path/to/Github_Webhook_Deploy* to your existing .gitignore file.
5. Change ownership of all of the directories and files so the web server user has proper permissions.  
`chown -R www-data:www-data {web server root}/{your website folder}/`
6. Confirm the auto.php file is accessible from a URL.  


#### Setup Github to use the webhook
---
Once your repository has been configured, the web server is configured, and this repo has been deployed and configured.  
The next step is to setup Github to send a push notification to the web server using a webhook.
1. Go to the repository and select 'Settings' from the top menu.

2. Select 'Webhooks' from the left menu.

3. Add a webhook.
	1. The 'Payload URL'will be the url used to access auto.php inside the *Github_Webhook_Deploy* folder we downloaded.  
	In my case the URL was `http://123.123.123.123/Github_Webhook_Deploy/auto.php`
	2. 'Content Type' needs to be changed to `application/json`
	3. Leave the rest of the settings in their defaults.
		*NOTE: If you do not have proper SSL certificates installed on your website, you will need to change the SSL verification setting.*
	4. Click 'Add webhook'
	5. After the webhook is saved, a ping is performed to test functionality.  
	If successful, a green checkmark will appear otherwise a red exclamation will appear.


#### Final steps
---
If you have reached this point, I assume you have completed:
- Setting up a web server.
- Creating and configuring a Github repository containing your website.
- Deploying your website files from your Github repository to your web server.
- Deploying this Github repository to your websites root directory.

Now we need to test everything.  
Commit a change to your Github repository containing your website.  
&emsp;***Make sure you either commit the change to the 'main' branch or merge another branch into the 'main' branch.***  
The changes should become live on the web server very quickly. (In my testing, changes seemed to be live in a matter of seconds.)


### Updating
---
Since you used git to download this repository, you can use git to update it if needed.
1. Change directory.  
`cd {web server root}/{your website folder}/Github_Webhook_Deploy/`

2. Pull the changes from the Github repository.  
`sudo -H -u www-data bash -c 'git pull origin main --depth=1 --rebase'`

3. Change ownership of all of the directories and files so the web server user has proper permissions.  
`chown -R www-data:www-data *`

4. Good to go again. :)


#### Additional features
---
I have taken the time to incorporate some additional features that may prove useful.

###### Verbose output
I have set the auto.php script to output nothing by default.  
However, you can edit the auto.php file and change the $verbose_output variable to get either status output or all output from the script.

###### Repository branch
I have set the auto.php script to pull git updates from the 'main' branch by default.  
However, you can edit the auto.php file and change the $git_branch variable to change which branch is pulled using Git.
