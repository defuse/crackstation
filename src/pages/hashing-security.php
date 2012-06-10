<div class="box">
<div class="padding">
			<h3>Salted Password Hashing - Doing it Right</h3>

			If you're a web developer, you've probably had to make a login system. If you've had to make a login system, you've had to use some form of hashing to protect your users' passwords in the event of a security breach. There are a lot of conflicting ideas and misconceptions on how to do password hashing properly. Password hashing is one of those things that's SO simple, but SO MANY people do it wrong. With this page, I hope to explain HOW to securely store passwords in a database, and WHY it should be done that way.
			<br /><br />

			<table id="shortcuts">
			<tbody><tr>
				<td>1. <a href="#normalhashing" title="What are hash functions and why are they used?">What is hashing?</a></td>
				<td>2. <a href="#attacks" title="Methods for making hash cracking more efficient">How Hashes are Cracked</a></td>

				<td>3. <a href="#ineffective" title="The wrong way to do password hashing">Ineffective Hashing Methods</a></td>
				<td>4. <a href="#salt" title="Adding salt to render hash cracking attacks less effective">What is salt?</a></td>
			</tr>
			<tr>
				<td>5. <a href="#properhashing" title="The right way to do password hashing, with salt">How to hash properly</a></td>
				<td>6. <a href="#faq" title="Frequently asked questions about password hashing and salt">Frequently Asked Questions</a></td>

				<td>7. <a href="#phpsourcecode" title="PHP password hashing example source code">PHP Source Code</a></td>
				<td>8. <a href="#aspsourcecode" title="PHP password hashing example source code in C#">ASP.NET (C#) Source Code</a></td>
			</tr>
			</tbody></table>

			<br /><br />
			<a name="normalhashing"></a>
			<h3>What is password hashing?</h3>
					<div class="passcrack" style="text-align: center;">

						hash("hello") = 2cf24dba5fb0a30e26e83b2ac5b9e29e1b161e5c1fa7425e73043362938b9824<br>
						hash("hbllo") = 58756879c05c68dfac9866712fad6a93f8146f337a69afe7dd238f3364946366<br>
						hash("waltz") = c0e81794384491161f1777c232bc6bd9ec38f616560b120fda8e90f383853542<br>
					</div>
			Hash algorithms are one way functions, meaning: they turn any amount of data into a fixed-length checksum that cannot be reversed. They also have the property that if the input changes by even a tiny bit, the resulting hash is COMPLETELY different. This is great for us, because we want to be able to be able to store passwords in an encrypted form that's impossible to decrypt. But at the same time, we need to be able to verify that a user's password is correct when they login. Generally, we follow this process:
			<br /><br />
			<ol class="moveul">
				<li>The user creates an account.</li>
				<li>Their password is hashed and stored in the database. At no point is the unhashed user's password ever written to the hard drive.</li>

				<li>When the user attempts to login, the hash of the password they entered is checked against the hash in the database.</li>
				<li>If the hashes match, the user is granted access. If not, the user is told they entered an incorrect password.</li>
				<li>Steps 3 and 4 repeat everytime someone tries to login to their account.</li>
			</ol>
			<br /><br />
			You may think that simply hashing passwords is enough to keep your users' passwords secure in the event of a database leak. Although normal hashing is FAR better than storing passwords in plain text (not hashed), there are a lot of ways to quickly recover passwords from normal hashes. We can do more to make cracking the hashes MUCH more difficult for someone who has stolen your database. If your users' passwords are only hashed, approximately 40% of the hashes can be cracked by a service like <a href="http://crackstation.net/">CrackStation</a> in the first day that someone gets a hold of your database.
			<a name="attacks"></a>

			<br /><br />
			<h3>How Hashes are Cracked</h3>
			<ul class="moveul" >
				<li>

					<h4>Dictionary and Brute Force Attacks</h4>
					<table style="margin: 0pt auto;">
					<tbody><tr>
					<td>
					<div class="passcrack" title="Cracking a hash by brute force">
						Trying aaaa : failed<br>
						Trying aaab : failed<br>

						Trying aaac : failed<br>
						&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;...			<br>
						Trying acdb : failed<br>
						<span style="color: green;">Trying acdc : success!</span><br>
					</div>
					</td>
					<td>

					<div class="passcrack" title="Cracking a hash with a wordlist">
						Trying apple : failed<br>
						Trying blueberry : failed<br>
						Trying justinbeiber : failed<br>
						&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;...			<br>
						Trying letmein : failed<br>

						<span style="color: green;">Trying secretpassword : success!</span><br>
					</div>
					</td>
					</tr>
					</tbody></table>
					<br>
					When you have a hash you want to crack, the simplest form of attack is to guess the password using word lists or password cracking dictionaries. That involves hashing every word in the list, and seeing if it's hash matches the hash you're trying to crack. If it does, then you have just found the password for that hash. Brute force attacks are the same as dictionary attacks except they don't use a word list; they try every possible combination of letters, numbers, and symbols.
					<br><br>
					There is no way to prevent dictionary attacks or brute force attacks. They can be made less effective, but there isn't a way to prevent them altogeather. If your password hashing system is secure, the only way to crack a hash will be to guess the correct password through a dictionary attack or brute force attack.
					<br /><br />
				</li>

				<li>
					<h4>Lookup Tables</h4>
					<div class="passcrack" style="text-align: center;" title="Cracking many hashes with a pre-computed lookup table">
						<span style="color: green;">Searching: 5f4dcc3b5aa765d61d8327deb882cf99: FOUND: password5</span><br>
						Searching: 6cbe615c106f422d23669b610b564800: &nbsp;not in database<br>
						<span style="color: green;">Searching: 630bf032efe4507f2c57b280995925a9: FOUND: letMEin12 </span><br>

						&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;...			<br>
						<span style="color: green;">Searching: 386f43fab5d096a7a66d67c8f213e5ec: FOUND: mcd0nalds</span><br>
						<span style="color: green;">Searching: d5ec75d5fe70d428685510fae36492d9: FOUND: p@ssw0rd!</span><br>
					</div>
					<br>
					Say you have a database of 1 Million hashes. You want to perform a dictionary attack on every hash, but you don't want to do 1 million dictionary attacks. What you do is hash every word in your dictionary, and store the word:hash pair in a lookup table. Next, you go through all the hashes you want to crack and see if the hash exists in the lookup table. If it does, you've just found the password. In this case the lookup table method is MUCH faster than doing 1 million dictionary attacks. You only have to hash each word in your wordlist once, then perform 1 million lookups (which are VERY fast). These lookup table databases DO exist! <a href="http://crackstation.net/">CrackStation</a> is one of them!
					<br /><br />
				</li>
				<li>

					<h4>Rainbow Tables</h4>
					Rainbow tables are a hybrid of lookup tables and brute force. In brief, they combine the two methods to reduce the overall size needed to store the wordlist. They do so by using a time-memory trade-off, making it take a little longer to crack one hash, but reducing the amount of hard drive space required to store the lookup table. For our purposes, we can think of lookup tables and rainbow tables as the same thing.
				</li>
	
			</ul>
            <br />
			<a name="ineffective"></a>
			<h3>The <span style="color: red;">WRONG</span> Way: Double Hashing &amp; Wacky Hash Functions</h3>
			This is a common one. The idea is that if you do something like <span class="ic">md5(md5($password))</span> or even <span class="ic">md5(sha1($password))</span> it will be more secure since plain md5 is "broken". I've even seen someone claim that it's better to use a super complicated function like <span class="ic">md5(sha1(md5(md5($password) + sha1($password)) + md5($password)))</span>. While complicated hash functions can sometimes be useful for generating encryption keys, you won't get much more security by combining hash functions. It's far better to choose a secure hash algorithm in the first place, and use <b>salt</b>, which I will discuss later. Once you are using salt, you can use multiple secure hash functions, for example <span class="ic">SHA256(WHIRLPOOL($password + $salt) + $salt)</span>. Combining secure hash functions will help if a practical collision attack is ever found for one of the hash algorithms, but it doesn't stop attackers from building lookup tables.

			<br><br>
			The attacks on MD5 are <b>collision</b> attacks. That means it's possible to find two different strings that have the same MD5 hash. If we were trying to prevent such an attack from affecting our cryptosystem, double hashing is the wrong thing to do. If you can find two strings of data such that <span class="ic">md5($data) == md5($differentData)</span>, then <span class="ic">md5(md5($data))</span> will STILL be the same as <span class="ic">md5(md5($differentData))</span>. Because the "inside" hashes are the same, so the "outside" hashes will be too. Adding the second hash did nothing. The collision attacks on MD5 don't make it any easier to recover the password from an md5 hash, but it's good practice to stop using MD5 just because there are much better functions readily available.

			<br><br>
			Double hashing does not protect against lookup tables or rainbow tables. It makes the process of generating the lookup table two times slower, but we want it to be <b>impossible</b> to use lookup tables. We can easily do so by adding "salt".
			<a name="salt"></a>
			<br /><br />
			<h3>Adding Salt</h3>
					<div class="passcrack" style="text-align: center;">
						hash("hello") = &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;2cf24dba5fb0a30e26e83b2ac5b9e29e1b161e5c1fa7425e73043362938b9824<br>
						hash("hello" + "QxLUF1bgIAdeQX") = 9e209040c863f84a31e719795b2577523954739fe5ed3b58a75cff2127075ed1<br>

						hash("hello" + "bv5PehSMfV11Cd") = d1d3ec2e6f20fd420d50e2642992841d8338a314b8ea157c9e18477aaef226ab
					</div>
			Salt is nothing complicated, just a string of random characters that get appended to the password before hashing. When done properly, it renders lookup tables and rainbow tables useless. Salt does so because, by adding extra characters, the resulting hash is COMPLETELY different than the unsalted hash of the password. For example, if the user's password was "apple", the SHA256 hash would be 

			<span class="ic">3a7bd3e2360a3d29eea436fcfb7e...</span>

			but if we append the salt, "Uwqe2uXdSKpAAi" before hashing, we get

			<span class="ic">b7d07a9b609b222a73c750584e69...</span> which has NO similarity AT ALL to the unsalted hash. Even a small change in the salt will result in a completely different hash, so if a lookup table has been created for unsalted hashes, it cannot be used to crack salted hashes. If a lookup table were created with the salt "HdK92TLAOP71", then it would be useless for cracking hashes that were salted with "EEbbTsLyddNO". When salting is done properly, it renders lookup tables and rainbow tables completely useless.
			<br /><br />
			<h3>The <span style="color: red;">WRONG</span> Way: Short Salt &amp; Salt Re-use</h3>

			The most common error of hash salting is using the same salt for every password. Someone trying to crack 1 million hashes that were all salted with the same salt would simply have to re-make his lookup table. He would just create a lookup table matching the words in a dictionary to the salted hashes of the words. He could then use the lookup table to VERY quickly attempt to crack all 1 million passwords.

			<br><br>
			The second most common error of hash salting is using a salt that's too short. Imagine 10 million password are hashed using random salts, but the salt is only 2 ASCII characters. Since there are only 95 printable ASCII characters, and the salt is only 2 characters long, there are <span class="ic">95<sup>2</sup> = 9 025</span> possible salt values. Since there are 10 million passwords, there will be <span class="ic">10 000 000 / 9 025 = 1 108</span> passwords using each salt. Someone trying to crack these hashes would make a lookup table for every possible salt value (9025 lookup tables), then use each lookup table to try to crack all the passwords that were using the same salt. The obvious fix to this problem is to use really long salt so that it's impossible to create a lookup table for every possible salt value.
			<br><br>
			It's also important not to rely on the username for salt. Usernames will be unique on YOUR website, but many other websites will have users of the same name. Someone trying to crack hashes would make a lookup table for every common username, and use them to crack hashes from different websites' databases. Since our goal is to have a unique and random salt for every password, using the username as salt has little security benefit.
			<a name="properhashing"></a>
			<br /><br />
			<h3>The <span style="color: green;">RIGHT</span> Way: How to Hash Properly</h3>

			To combat lookup tables and rainbow tables, all we have to do is give each password a long unique salt. Since no two passwords will ever be hashed using the same salt, and since there are so many possible salt values, lookup tables and rainbow tables become useless. The only way to recover the password from a hash with a unique salt is to guess the password (dictionary attack) or perform a brute force attack.
			<br><br>
			To guarantee the uniquness of the salt, it's best to use a randomly generated salt that's at least as long as the output of the hash function. If your hash function has a 256 bit output, then use 256 bits of salt. I find that the easiest way to ensure you're getting enough salt is to generate a random hex string that's the same length as the hash output. Make sure you use a <u>Cryptographically Secure</u> Pseudo-Random Number Generator (CSPRNG). Do NOT use your language's math library's <span class="ic">rand()</span> function. There will be a proper CSPRNG for you to use. In php, it's <a href="http://php.net/manual/en/function.mcrypt-create-iv.php" rel="nofollow"><span class="ic">mcrypt_create_iv()</span></a> and in .NET it's <a href="http://msdn.microsoft.com/en-us/library/system.security.cryptography.rngcryptoserviceprovider.aspx" rel="nofollow"><span class="ic">System.Security.Cryptography.RNGCryptoServiceProvider</span></a>. Since you want each password to have it's own salt, it's important to change the salt whenever the password is changed.
			<br><br>
			You only need to generate the salt when an account is created or a user changes their password. You store the salt in your database so that it can be used to validate the user's password when they login. The salt doesn't have to be secret at all. All that matters is that it's <b>unique</b> for every hash that's stored in your database.

			<br><br>
			The salt need not be secret because it's only purpose is to make sure that if two users have the same password, the hash of their passwords will be different. Once the password has been hashed with the salt, there's no way that the salt can be "removed" from the hash, even if it is known by the password cracker.
			<br><br>
            <h4>To Store a Password:</h4>
			<ol class="moveul">
				<li>Generate a long random salt using a CSPRNG.</li>
				<li>Compute <span class="ic">$hash = Hash($password . $salt)</span>, where Hash() is a strong hash function like SHA256.</li>
				<li>Save $hash and $salt in the database.</li>

			</ol>
            <h4>To Validate a Password:</h4>
			<ol class="moveul">
				<li>Get the $hash and $salt for that user from the database.</li>
				<li>Compute the hash of the password they tried to login with. <span class="ic">$userhash = Hash($pass_to_check . $salt)</span>.</li>
				<li>Compare $hash with $userhash. If they are EXACTLY the same, then the password is valid. If there is any difference, then the password is invalid.</li>
			</ol>
			<br>
			Instead of using multiple hash functions or creating your own, just stick to one well known and well tested algorithm. All you need is one. I would reccomend using SHA256.
			<br>
			<h4>Rules of thumb:</h4>
			<ul class="moveul">
				<li>Use a well-known and secure hash algorithm like SHA256.</li>
				<li>Each password should be hashed with a different salt.</li>
				<li>Salt should be a random string of characters at least AS LONG AS the output of the hash function.</li>
				<li>Use a CSPRNG to generate salt, NOT your language's built in <span class="ic">rand()</span> function.</li>

				<li>When passwords are changed, the salt must be changed.</li>
			</ul>
			<a name="faq"></a>
			<h3>FAQ</h3>
			<h4>What hash algorithm should I use?</h4>
			<span style="color: green;"><b>DO</b></span> use:

            <p>
			<ul class="moveul">
				<li>The SHA2 Family - SHA256 and SHA512</li>

				<li>RipeMD160</li>
				<li>WHIRLPOOL</li>
				<li>The <a href="#phpsourcecode" title="PHP password hashing source code">PHP source code</a> or the <a href="#aspsourcecode" title="C# password hashing source code">C# source code</a> near the bottom of this page</li>
			</ul>
            </p>
			<span style="color: red;"><b>DO NOT</b></span> use:

            <p>
			<ul class="moveul">
				<li>MD5</li>
				<li>SHA0 or SHA1</li>
				<li>Old versions of crypt. If you use a newer form of crypt, make sure it uses a long salt.</li>
				<li>Any algorithm that you made yourself or hasn't gone through an intensive peer review process like the SHA3 competition</li>
			</ul>
            </p>

            <p>
                Even though there are no attacks on MD5 or SHA1 that would help crack a hash, they have been superseded by the SHA2 family, so I don't recommend using them. An exception to this rule is PBKDF2, which is frequently used with SHA1.
            </p>
			<h4>How long should the salt be?</h4>
			The salt should be at least as long as the hash function. For example, if your hash function is 256 bits, then you should have a salt of at least 256 bits. I find that the easiest way to generate enough salt is to generate a random string of hex characters that is the same length as the hash function output (64 hex characters for 256 bits). First and foremost, your salt should be long enough so that no two users' passwords will ever be hashed using the same salt.

			<h4>How do I generate the salt?</h4>
			Use a <u>Cryptographically Secure</u> Pseudo-Random Number Generator (CSPRNG). Do NOT use your language's math library's <span class="ic">rand()</span> function. There will be a proper CSPRNG for you to use. In PHP, it's <a href="http://php.net/manual/en/function.mcrypt-create-iv.php" rel="nofollow" title="PHP mcrypt_create_iv Documentation"><span class="ic">mcrypt_create_iv()</span></a> and in .NET it's <a href="http://msdn.microsoft.com/en-us/library/system.security.cryptography.rngcryptoserviceprovider.aspx" title="C# RNGCryptoServiceProvider Documentation" rel="nofollow"><span class="ic">System.Security.Cryptography.RNGCryptoServiceProvider</span></a>. The imporant thing is that the salt is <b>unique</b> for each user. Using a high quality CSPRNG to generate a long salt will practically guarantee uniqueness without needing to manually check if the salt has been used before.


			<h4>What do I do if my database gets leaked/hacked?</h4>
            <p>It may be tempting to cover up the breach and hope nobody notices. Keep in mind that trying to cover up a breach makes you look worse, because you're putting your users at further risk by not informing them that their password may be compromised. The correct thing to do is to inform your users as soon as possible. <b>Do not inform your users by email.</b> Instead, put a notice on the front page of your website that links to a page with more detailed information. 
            </p>
            <p>
                You shouldn't inform your users by email because it will look like a <a href="http://en.wikipedia.org/wiki/Phishing">phishing attack</a>. Spam filters will block it, and security-aware users won't believe it.
            </p>
            <p>
                Explain to your users exactly how their passwords were protected&mdash;hopefully hashed with salt&mdash;and that even though they were protected with a salted hash, a malicious hacker can still run dictionary and brute force attacks on the hashes. Malicious hackers will use any passwords they find to try to log into a user's account on a different website, hoping they used the same password on both websites. Inform your users of this risk and recommend that they change there password both on your website, and any other website where they used a similar password.
            </p>

			<h4>What should my password policy be? Should I enforce strong passwords?</h4>
			Don't limit your users. I would reccomend somehow dynamically showing users the strength of their password as they type it, and let them decide how secure they want their password to be. If your service handles sensitive user information, you may want to ensure that there is at least 1 number and 1 symbol in the password. Passwords should be able to contain ANY type of character. The password length should be a minimum of 6 characters and a maximum of beyond 100 characters (Yes, There are people who use 100 character and longer passwords!).

			<h4>If someone has access to my database, can't they just replace the hash of my password with their own hash so they can login?</h4>
			Yes. But if someone has accesss to your database, they probably already have access to everything on your server, so they wouldn't need to login to your account to get what they want.
			<br /><br />
			However, there is something you can do to prevent this. Create special database permissions so that only the account creation script has <u>write</u> access to the user table, and give all other scripts <u>read only</u> access. Then, if an attacker can access your user table through a SQL injection vulnerability, he won't be able to modify the hashes.
			<h4>Is there anything that can be done to make dictionary attacks and brute force attacks harder?</h4>

            <p>
			Yes. You can have your program  hash the password many thousands of times (feed the output back into the input). Doing so makes the password hashing process thousands of times slower, and thus makes dictionary and brute force attacks thousands of times slower. This is called <a href="https://secure.wikimedia.org/wikipedia/en/wiki/Key_stretching">key stretching</a>. A common key stretching algorithm is <a href="https://secure.wikimedia.org/wikipedia/en/wiki/PBKDF2">PBKDF2</a>. If you want to use PBKDF2 in PHP, use <a href="https://defuse.ca/php-pbkdf2.htm">Defuse Cyber-Security's implementation</a>.
            </p>

            <p>You may think that having to hash the password thousands of times will slow down the login process, but an average CPU today can compute over 1,000,000 hashes in one second. The time it takes to hash a password, say, 5000 times, is not noticeable. This will increase your application's CPU usage, but it is well worth it. If a dictionary attack takes 10 minutes to run on a normal hash, it will take more than 34 days to run on a hash produced by 5000-iteration PBKDF2.
            </p>

            <p>
                I recommend using at least 500 iterations, but ideally 5000, for web applications that process a lot of sign-ons. A stand-alone application running on a PC or mobile device should use at least 50,000 iterations. But remember that computers are continually getting faster. You should increase the number of iterations as processor speed increases.
            </p>

			<h4>Why bother hashing?</h4>

            <p>
                Your users are entering their password into your website. They are trusting you with their security. If your database gets hacked, and your users' passwords are unprotected, then malicious hackers can use those passwords to compromise your users' accounts on other websites and services (most people use the same password everywhere). It's not just your security that's at risk, it's your users'. You are responsible for your users' security.
            </p>

			<a name="phpsourcecode"></a>
			<h3>PHP Password Hashing Code</h3>

			The following is a secure implementation of salted hashing in PHP. If you want to use PBKDF2 in PHP, use <a href="https://defuse.ca/php-pbkdf2.htm">Defuse Cyber-Security's implementation</a>.<br /><br />
<div class="passcrack">
//Takes a password and returns the salted hash<br />
//$password - the password to hash<br />
//returns - the hash of the password (128 hex characters)<br />
function HashPassword($password)<br />
{<br />
&nbsp;&nbsp; &nbsp;$salt = bin2hex(mcrypt_create_iv(32, MCRYPT_DEV_URANDOM)); //get 256 random bits in hex<br />
&nbsp;&nbsp; &nbsp;$hash = hash(&quot;sha256&quot;, $salt . $password); //prepend the salt, then hash<br />
&nbsp;&nbsp; &nbsp;//store the salt and hash in the same string, so only 1 DB column is needed<br />
&nbsp;&nbsp; &nbsp;$final = $salt . $hash; <br />
&nbsp;&nbsp; &nbsp;return $final;<br />
}<br />
<br />
//Validates a password<br />
//returns true if hash is the correct hash for that password<br />
//$hash - the hash created by HashPassword (stored in your DB)<br />
//$password - the password to verify<br />
//returns - true if the password is valid, false otherwise.<br />
function ValidatePassword($password, $correctHash)<br />
{<br />
&nbsp;&nbsp; &nbsp;$salt = substr($correctHash, 0, 64); //get the salt from the front of the hash<br />
&nbsp;&nbsp; &nbsp;$validHash = substr($correctHash, 64, 64); //the SHA256<br />
<br />
&nbsp;&nbsp; &nbsp;$testHash = hash(&quot;sha256&quot;, $salt . $password); //hash the password being tested<br />
&nbsp;&nbsp; &nbsp;<br />
&nbsp;&nbsp; &nbsp;//if the hashes are exactly the same, the password is valid<br />
&nbsp;&nbsp; &nbsp;return $testHash === $validHash;<br />
}
</div>
			<a name="aspsourcecode"></a>
			<h3>ASP.NET (C#) Password Hashing Code</h3>
			The following code is a secure implementation of salted hashing in C# for ASP.NET<br /><br />
<div class="passcrack">
using System;<br />
using System.Text;<br />
using System.Security.Cryptography;<br />
<br />
namespace DEFUSE<br />
{<br />
&nbsp;&nbsp; &nbsp;/*<br />
&nbsp;&nbsp; &nbsp; * PasswordHash - A salted password hashing library<br />
&nbsp;&nbsp; &nbsp; * WWW: https://defuse.ca/<br />
&nbsp;&nbsp; &nbsp; * Use:<br />
&nbsp;&nbsp; &nbsp; * &nbsp; &nbsp; &nbsp;Use &#039;HashPassword&#039; to create the initial hash, store that in your DB<br />
&nbsp;&nbsp; &nbsp; * &nbsp; &nbsp; &nbsp;Then use &#039;ValidatePassword&#039; with the hash from the DB to verify a password<br />
&nbsp;&nbsp; &nbsp; * &nbsp; &nbsp; &nbsp;NOTE: Salting happens automatically, there is no need for a separate salt field in the DB<br />
&nbsp;&nbsp; &nbsp; */<br />
&nbsp;&nbsp; &nbsp;class PasswordHash<br />
&nbsp;&nbsp; &nbsp;{<br />
&nbsp;&nbsp; &nbsp;/// &lt;summary&gt;<br />
&nbsp;&nbsp; &nbsp;/// Hashes a password<br />
&nbsp;&nbsp; &nbsp;/// &lt;/summary&gt;<br />
&nbsp;&nbsp; &nbsp;/// &lt;param name=&quot;password&quot;&gt;The password to hash&lt;/param&gt;<br />
<br />
&nbsp;&nbsp; &nbsp;/// &lt;returns&gt;The hashed password as a 128 character hex string&lt;/returns&gt;<br />
&nbsp;&nbsp; &nbsp;public static string HashPassword(string password)<br />
&nbsp;&nbsp; &nbsp;{<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp;string salt = GetRandomSalt();<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp;string hash = Sha256Hex(salt + password);<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp;return salt + hash;<br />
&nbsp;&nbsp; &nbsp;}<br />
<br />
&nbsp;&nbsp; &nbsp;/// &lt;summary&gt;<br />
&nbsp;&nbsp; &nbsp;/// Validates a password<br />
&nbsp;&nbsp; &nbsp;/// &lt;/summary&gt;<br />
&nbsp;&nbsp; &nbsp;/// &lt;param name=&quot;password&quot;&gt;The password to test&lt;/param&gt;<br />
<br />
&nbsp;&nbsp; &nbsp;/// &lt;param name=&quot;correctHash&quot;&gt;The hash of the correct password&lt;/param&gt;<br />
&nbsp;&nbsp; &nbsp;/// &lt;returns&gt;True if password is the correct password, false otherwise&lt;/returns&gt;<br />
&nbsp;&nbsp; &nbsp;public static bool ValidatePassword(string password, string correctHash )<br />
&nbsp;&nbsp; &nbsp;{<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp;if (correctHash.Length &lt; 128)<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp;throw new ArgumentException(&quot;correctHash must be 128 hex characters!&quot;);<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp;string salt = correctHash.Substring(0, 64);<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp;string validHash = correctHash.Substring(64, 64);<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp;string passHash = Sha256Hex(salt + password);<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp;return string.Compare(validHash, passHash) == 0;<br />
&nbsp;&nbsp; &nbsp;}<br />
<br />
&nbsp;&nbsp; &nbsp;//returns the SHA256 hash of a string, formatted in hex<br />
&nbsp;&nbsp; &nbsp;private static string Sha256Hex(string toHash)<br />
&nbsp;&nbsp; &nbsp;{<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp;SHA256Managed hash = new SHA256Managed();<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp;byte[] utf8 = UTF8Encoding.UTF8.GetBytes(toHash);<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp;return BytesToHex(hash.ComputeHash(utf8));<br />
&nbsp;&nbsp; &nbsp;}<br />
<br />
&nbsp;&nbsp; &nbsp;//Returns a random 64 character hex string (256 bits)<br />
&nbsp;&nbsp; &nbsp;private static string GetRandomSalt()<br />
&nbsp;&nbsp; &nbsp;{<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp;RNGCryptoServiceProvider random = new RNGCryptoServiceProvider();<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp;byte[] salt = new byte[32]; //256 bits<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp;random.GetBytes(salt);<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp;return BytesToHex(salt);<br />
&nbsp;&nbsp; &nbsp;}<br />
<br />
&nbsp;&nbsp; &nbsp;//Converts a byte array to a hex string<br />
&nbsp;&nbsp; &nbsp;private static string BytesToHex(byte[] toConvert)<br />
&nbsp;&nbsp; &nbsp;{<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp;StringBuilder s = new StringBuilder(toConvert.Length * 2);<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp;foreach (byte b in toConvert)<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp;{<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp;s.Append(b.ToString(&quot;x2&quot;));<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp;}<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp;return s.ToString();<br />
&nbsp;&nbsp; &nbsp;}<br />
&nbsp;&nbsp; &nbsp;}<br />
}
</div>
			</div>
			<br />
            <div style="text-align: center;">
			    <h4>Article written by <a href="https://defuse.ca/">Defuse Cyber-Security.</a></h4>
            </div>

</div>
</div>
