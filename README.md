# LinkBait

Linkbait is a PHP file used to store browser and connection information on anyone who loads the webpage.

## Installation

Copy the PHP file into a web-accessable directory on your Apache web server. Most shared hosts use PHP, so that should not be an issue. 


## Usage 
When someone loads the PHP file, it will create a session cookie and link it to a file containing their IP address and User-Agent and send them the HTML/Javascript that the logger uses to grab the browser fingerprint. Once the javascript is done executing it will send a request back to the server with all of the collected data which then gets added to the log file. If the user opens the page again, it will gather all the data again and add it to the **same** log file as a way to keep each targets data together.

It is recommended that you use the included .htaccess file to redirect any requests to non-existing files to logger.php, and to prevent anyone from accessing your output/ directory from the web.

When sending a URL to the target (if using the included .htaccess file, or an equivalent one) you can send a customized url such as
```
https://mydomain.com/random/check2-email-jesse
```
or 
```
https://mydomain.com/emailwhois?email=jesse@notarealemail.com&name=jesse
```


The included example.html file can be moved outside of the output/ directory to allow you to test the service without sending any information to your server. 


The script collects the following information

- Touch Information
- Router IP Address
- Installed GPU
- Browser Version/Platform
- Screen height/width
- If the user has any webcam or mic plugged in
- The users IPV6 address (if available)
- If the user has discord running
- Installed browser fonts
- It checks a select few sites to see if the user is logged in
- It checks if a select few extensions are installed (Chrome Only) 
- System Time
- System Language
- TCP connection information (If they're using a VPN, and their operating system
- IPV4 address (php file only, not in example.html)
- User Agent (php file only, not in example.html)


## Contributing
Pull requests are welcome. For major changes or any features you would like to see, please open an issue first to discuss what you would like to see changed.

## License
[MIT](https://choosealicense.com/licenses/mit/)
