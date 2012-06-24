<div class="box">
<div class="padding">
			<h3>Salted Password Hashing - Doing it Right</h3>

<p>
If you're a web developer, you've probably had to make a user account system.
The most important aspect of a user account system is how user passwords are
protected. User account databases are hacked frequently, so you absolutely must
do something to protect your users' passwords if your website is ever breached.
The best way to protect passwords is to employ <b>salted password hashing</b>.
This page will explain how to do it properly.
</p>

<p>
There are a lot of conflicting ideas and misconceptions on how to do password
hashing properly, probably due to the abundance of misinformation on the web.
Password hashing is one of those things that's so simple, but yet so many people
get wrong. With this page, I hope to explain not only the correct way to do it,
but why it should be done that way.
</p>

<p>You may use the following links to jump to the different sections of this page.</p>
<table id="shortcuts">
<tbody><tr>
    <td>1. <a href="#normalhashing" title="What are hash functions and why are they used?">What is password hashing?</a></td>
    <td>2. <a href="#attacks" title="Methods for making hash cracking more efficient">How Hashes are Cracked</a></td>

    <td>3. <a href="#salt" title="Adding salt to render hash cracking attacks less effective">Adding Salt</a></td>
    <td>4. <a href="#ineffective" title="The wrong way to do password hashing">Ineffective Hashing Methods</a></td>
</tr>
<tr>
    <td>5. <a href="#properhashing" title="The right way to do password hashing, with salt">How to hash properly</a></td>
    <td>6. <a href="#faq" title="Frequently asked questions about password hashing and salt">Frequently Asked Questions</a></td>

    <td>7. <a href="#phpsourcecode" title="PHP password hashing example source code">PHP Source Code</a></td>
    <td>8. <a href="#aspsourcecode" title="PHP password hashing example source code in C#">ASP.NET (C#) Source Code</a></td>
</tr>
</tbody></table>

<a name="normalhashing"></a>
<h3>What is password hashing?</h3>
        <div class="passcrack" style="text-align: center;">
            hash("hello") = 2cf24dba5fb0a30e26e83b2ac5b9e29e1b161e5c1fa7425e73043362938b9824<br>
            hash("hbllo") = 58756879c05c68dfac9866712fad6a93f8146f337a69afe7dd238f3364946366<br>
            hash("waltz") = c0e81794384491161f1777c232bc6bd9ec38f616560b120fda8e90f383853542<br>
        </div>
<p>
Hash algorithms are one way functions. They turn any amount of data into a
fixed-length "fingerprint" that cannot be reversed. They also have the property
that if the input changes by even a tiny bit, the resulting hash is completely
different (see the example above). This is great for protecting passwords,
because we want to store passwords in an encrypted form that's impossible to
decrypt, but at the same time, we need to be able to verify that a user's
password is correct. 
</p>

<p>
The general workflow for account registration and authentication in a hash-based
account system is as follows:
</p>
<ol class="moveul">
    <li>The user creates an account.</li>
    <li>Their password is hashed and stored in the database. At no point is the plain-text (unencrypted) password ever written to the hard drive.</li>

    <li>When the user attempts to login, the hash of the password they entered is checked against the hash of their real password (retrieved from the database).</li>
    <li>If the hashes match, the user is granted access. If not, the user is told they entered an incorrect password.</li>
    <li>Steps 3 and 4 repeat everytime someone tries to login to their account.</li>
</ol>
<br />
<p>
It should be noted that the hash functions used to protect passwords are not the
same as the hash functions you may have seen in a data structures course.  The
hash functions used to implement data structures such as hash tables are
designed to be fast, not secure. Only <b>cryptographic hash functions</b> may be
used to implement password hashing.  Hash functions like SHA256, SHA512, RipeMD,
and WHIRLPOOL are cryptographic hash functions.
</p>

<p>
It is easy to think that all you have to do is run the password through a
cryptographic hash function and your users' passwords will be secure. This is
far from the truth. There are many ways to recover passwords from plain hashes
very quickly. There are several easy-to-implement techniques that make these
"attacks" much less effective. To motivate the need for these techniques,
consider this very website. On the front page, you can submit a list of hashes
to be cracked, and receive results in less than a second. Clearly, simply
hashing the password does not meet our needs for security.
</p>

<p>The next section will discuss some of the common attacks used to crack plain password hashes.</p>

<a name="attacks"></a>
<h3>How Hashes are Cracked</h3>
<ul class="moveul" >
<li>
    <h4>Dictionary and Brute Force Attacks</h4>
    <table style="margin: 0 auto;">
    <tbody><tr>
    <td>
    <div class="passcrack" title="Cracking a hash with a wordlist">
        <center>Dictionary Attack</center><br />
        Trying apple &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;: failed<br>
        Trying blueberry &nbsp;&nbsp;&nbsp;: failed<br>
        Trying justinbeiber : failed<br>
        <center>...</center>
        Trying letmein &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;: failed<br>

        <span style="color: green;">Trying s3cr3t &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;: success!</span><br>
    </div>
    </td>
    <td>
    <div class="passcrack" title="Cracking a hash by brute force">
        <center>Brute Force Attack</center><br />
        Trying aaaa : failed<br>
        Trying aaab : failed<br>
        Trying aaac : failed<br>
        <center>...</center>
        Trying acdb : failed<br>
        <span style="color: green;">Trying acdc : success!</span><br>
    </div>
    </td>
    </tr>
    </tbody></table>
    <p>
    The simplest way to crack a hash is to try to guess the password, hashing each guess, and checking if the guess's hash equals the hash being cracked. If the hashes are equal, the guess is the password.
    The two most common ways of guessing passwords are <b>dictionary attacks</b> and <b>brute-force attacks</b>.
    </p>

    <p>
    A dictionary attack uses a file containing words, phrases, common passwords,
    and other strings that are likely to be used as a password. Each word in the
    file is hashed, and its hash is compared to the password hash. If they
    match, that word is the password. These dictionary files are constructed by
    extracting words from large bodies of text, and even from real databases of
    passwords. Further processing is often applied to dictionary files, such as
    replacing words with their "leet speak" equivalents ("hello" becomes
    "h3110"), to make them more effective.
    </p>

    <p>
    A brute-force attack tries every possible combination of characters up to a
    given length. These attacks are very computationally expensive, and are
    usually the least efficient in terms of hashes cracked per processor time,
    but they will always eventually find the password. Passwords should be long
    enough that searching through all possible character strings to find it will
    take too long to be worthwhile.
    </p>

    <p>
    There is no way to prevent dictionary attacks or brute force attacks. They
    can be made less effective, but there isn't a way to prevent them
    altogether. If your password hashing system is secure, the only way to crack
    the hashes will be to run a dictionary or brute-force attack on each hash.
    </p>
</li>

<li>
    <h4>Lookup Tables</h4>
    <center>
    <span class="passcrack" style="display: inline-block; text-align: left;" title="Cracking many hashes with a pre-computed lookup table">
        <span style="color: green;">Searching: 5f4dcc3b5aa765d61d8327deb882cf99: FOUND: password5</span><br>
        Searching: 6cbe615c106f422d23669b610b564800: &nbsp;not in database<br>
        <span style="color: green;">Searching: 630bf032efe4507f2c57b280995925a9: FOUND: letMEin12 </span><br>
        <span style="color: green;">Searching: 386f43fab5d096a7a66d67c8f213e5ec: FOUND: mcd0nalds</span><br>
        <span style="color: green;">Searching: d5ec75d5fe70d428685510fae36492d9: FOUND: p@ssw0rd!</span><br>
    </span>
    </center>

    <p>
    Lookup tables are an extremely effective method for cracking many hashes of
    the same type very quickly. The general idea is to <b>pre-compute</b> the
    hashes of the passwords in a password dictionary and store them, and their
    corresponding password, in a lookup table data structure. A good
    implementation of a lookup table can process hundreds of hash lookups per
    second, even when they contain many billions of hashes.
    </p>

    <p>
    If you want a better idea of how fast lookup tables can be, try cracking the
    following sha256 hashes with CrackStation's <a href="/">free hash
    cracker</a>.
    </p>

    <div class="passcrack" style="text-align: center;" title="Example hashes to be cracked">
    c11083b4b0a7743af748c85d343dfee9fbb8b2576c05f3a7f0d632b0926aadfc
    08eac03b80adc33dc7d8fbe44b7c7b05d3a2c511166bdb43fcb710b03ba919e7
    e4ba5cbd251c98e6cd1c23f126a3b81d8d8328abc95387229850952b3ef9f904
    5206b8b8a996cf5320cb12ca91c7b790fba9f030408efe83ebb83548dc3007bd
    </div>

</li>

<li>
    <h4>Reverse Lookup Tables</h4>
    <center>
    <span class="passcrack" style="display: inline-block; text-align: left;" title="Cracking many hashes with a pre-computed lookup table">
        <span style="color: green;">Searching for hash(apple) in users' hash list... &nbsp;&nbsp;&nbsp;&nbsp;: Matches [alice3, 0bob0, charles8]</span><br>
        <span style="color: green;">Searching for hash(blueberry) in users' hash list... : Matches [usr10101, timmy, john91]</span><br>
        <span style="color: green;">Searching for hash(letmein) in users' hash list... &nbsp;&nbsp;: Matches [wilson10, dragonslayerX, joe1984]</span><br>
        <span style="color: green;">Searching for hash(s3cr3t) in users' hash list... &nbsp;&nbsp;&nbsp;: Matches [bruce19, knuth1337, john87]</span><br>
        <span>Searching for hash(z@29hjja) in users' hash list... &nbsp;: No users used this password</span><br>
    </span>
    </center>
    <p>
        This attack allows an attacker to apply a dictionary or brute-force attack to many hashes at the same time, without having to pre-compute a lookup table.
    </p>

    <p>
    First, the attacker creates a lookup table that maps each password hash from
    the compromised user account database to a list of users who had that hash.
    The attacker then hashes each password guess and uses the lookup table to
    get a list of users whose password was the attacker's guess. This attack is
    especially effective because it is common for many users to have the same
    password. 
    </p>
</li>

<li>
    <h4>Rainbow Tables</h4>
    <p>
    Rainbow tables are a time-memory trade-off technique. They are like lookup
    tables, except that they sacrifice hash cracking speed to make the lookup
    tables smaller. Because they are smaller, the solutions to more hashes can
    be stored in the same amount of space, making them more effective. Rainbow
    tables that can crack any md5 hash of a password up to 8 characters long <a
    href="http://www.freerainbowtables.com/en/tables2/">exist</a>.
    </p>
</li>
</ul>

<p>
Next, we'll look at a technique called salting, which makes it impossible to use
lookup tables and rainbow tables to crack a hash.
</p>

<a name="salt"></a>
<h3>Adding Salt</h3>
<div class="passcrack" style="text-align: center;" title="Salt example">
    hash("hello") &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; = 2cf24dba5fb0a30e26e83b2ac5b9e29e1b161e5c1fa7425e73043362938b9824<br />
    hash("hello" + "QxLUF1bgIAdeQX") = 9e209040c863f84a31e719795b2577523954739fe5ed3b58a75cff2127075ed1<br />

    hash("hello" + "bv5PehSMfV11Cd") = d1d3ec2e6f20fd420d50e2642992841d8338a314b8ea157c9e18477aaef226ab<br />
    hash("hello" + "YYLmfY6IehjZMQ") = a49670c3c18b9e079b9cfaf51634f563dc8ae3070db2c4a8544305df1b60f007
</div>

<p>
Lookup tables and rainbow tables only work because each password is hashed the
exact same way. If two users have the same password, they'll have the same
password hashes.  We can prevent these attacks by randomizing each hash, so that
when the same password is hashed twice, the hashes are not the same.
</p>

<p>
We can randomize the hashes by appending or prepending a random string, called a
<b>salt</b>, to the password before hashing. As shown in the example above, this
makes the same password hash into a completely different string every time. To
check if a password is correct, we need the salt, so it is usually stored in the
user account database along with the hash, or as part of the hash string itself.
</p>

<p>
The salt does not need to be secret. Just by randomizing the hashes, lookup
tables, reverse lookup tables, and rainbow tables become ineffective. An
attacker won't know in advance what the salt will be, so they can't pre-compute
a lookup table or rainbow table. If each user's password is hashed with a
different salt, the reverse lookup table attack won't work either. 
</p>

<p>
In the next section, we'll look at how salt is commonly implemented incorrectly.
</p>

<a name="ineffective"></a>
<h3>The <span style="color: red;">WRONG</span> Way: Short Salt &amp; Salt Reuse</h3>

<p>
The most common salt implementation errors are reusing the same salt in multiple
hashes, or using a salt that is too short.
</p>

<h4>Salt Reuse</h4>

<p>
A common mistake is to use the same salt in each hash. Either the salt is
hard-coded into the program, or is generated randomly once. This is ineffective
because if two users have the same password, they'll still have the same hash.
An attacker can still use a reverse lookup table attack to run a dictionary
attack on every hash at the same time. They just have to apply the salt to each
password guess before they hash it. If the salt is hard-coded into a popular
product, lookup tables and rainbow tables can be built for that salt, to make it
easier to crack hashes generated by the product.
</p>

<p>
A new random salt must be generated each time a user creates an account or changes their password.
</p>

<h4>Short Salt</h4>

<p>
If the salt is too short, an attacker can build a lookup table for every
possible salt. For example, if the salt is only three ASCII characters, there
are only 95x95x95 = 857,375 possible salts. That may seem like a lot, but if
each lookup table contains only 1MB of the most common passwords, collectively
they will be only 837GB, which is not a lot considering 1000GB hard drives can
be bought for under $100 today.
</p>

<p>
For the same reason, the username shouldn't be used as a salt. Usernames may be
unique to a single service, but they are predictable and often reused for
accounts on other services.  An attacker can build lookup tables for common
usernames and use them to crack username-salted hashes.
</p>

<p>
To make it impossible for an attacker to create a lookup table for every
possible salt, the salt must be long. A good rule of thumb is to use a salt that
is the same size as the output of the hash function. For example, the output of
SHA256 is 256 bits (32 bytes), so the salt should be at least 32 random bytes.
</p>

<h3>The <span style="color: red;">WRONG</span> Way: Double Hashing &amp; Wacky Hash Functions</h3>
<p>
This section covers the most common password hashing misconception: wacky
combinations of hash algorithms. It's easy to get carried away and try to
combine different hash functions, hoping that the result will be more secure. In
practice, though, there is no benefit to doing it. All it does is create
interoperability problems, and can sometimes even make the hashes less secure.
Never try to invent your own crypto, always use a standard that has been
designed by experts. Some will argue that using multiple hash functions makes
the process of computing the hash slower, so cracking is slower, but there's a
better way to make the cracking process slower as we'll see later.
</p>

<p>Here are some examples of poor wacky hash functions I've seen suggested in forums on the internet.</p>

<ul>
    <li><span class="ic">md5(sha1(password))</span></li>
    <li><span class="ic">md5(md5(salt) + md5(password))</span></li>
    <li><span class="ic">sha1(sha1(password))</span></li>
    <li><span class="ic">sha1(str_rot13(password + salt))</span></li>
    <li><span class="ic">md5(sha1(md5(md5(password) + sha1(password)) + md5(password)))</span></li>
</ul> <br />

<p>
    Do not use any of these. None of the wacky combinations provide any additional security. 
</p>

<h3>Hash Collisions</h3>

<p>
Because hash functions map arbitrary amounts of data to fixed-length strings,
there must be some inputs that hash into the same string. Cryptographic hash
functions are designed to make these collisions incredibly difficult to find.
From time to time, cryptographers find "attacks" on hash functions that make
finding collisions easier. A recent example is the MD5 hash function, for which
collisions have actually been found.
</p>

<p>
Collision attacks are a sign that it may be more likely for a string other than
the user's password to have the same hash. However, finding collisions in even a
weak hash function like MD5 requires a lot of dedicated computing power, so it
is very unlikely that these collisions will happen "by accident" in practice. A
password hashed using MD5 and salt is, for all practical purposes, just as
secure as if it were hashed with SHA256 and salt. Nevertheless, it is a good
idea to use a more secure hash function like SHA256, SHA512, RipeMD, or
WHIRLPOOL if possible.
</p>

<a name="properhashing"></a>
<h3>The <span style="color: green;">RIGHT</span> Way: How to Hash Properly</h3>

<p>
This section describes exactly how passwords should be hashed. The first
subsection covers the basics&mdash;everything that is absolutely necessary. The
following subsections explain how the basics can be augmented to make the hashes
even harder to crack.
</p>

<h4>The Basics: Hashing with Salt</h4>

<p>
We've seen how malicious hackers can crack plain hashes very quickly using
lookup tables and rainbow tables. We've learned that randomizing the hashing
using salt is the solution to the problem.  But how do we generate the salt, and
how do we apply it to the password?
</p>

<p>
Salt should be generated using a <b>Cryptographically Secure Pseudo-Random
Number Generator</b> (CSPRNG). CSPRNGs are very different than ordinary
pseudo-random number generators, like the "C" language's 
<span class="ic">rand()</span> function.  As the name suggests, CSPRNGs are
designed to be cryptographically secure, meaning they provide a high level of
randomness and are completely unpredictable. We don't want our salts to be
predictable, so we must use a CSPRNG. The following table lists some CSPRNGs
that exist for some popular programming platforms.
</p>

<table id="rnglist">
    <tr><th>Platform</th><th>CSPRNG</th></tr>
    <tr><td>PHP</td><td><a href="http://php.net/manual/en/function.mcrypt-create-iv.php">mcrypt_create_iv</a>, <a href="http://php.net/manual/en/function.openssl-random-pseudo-bytes.php">openssl_random_pseudo_bytes</a></td></tr>
    <tr><td>Dot NET (C#, VB)</td><td><a href="http://msdn.microsoft.com/en-us/library/system.security.cryptography.rngcryptoserviceprovider.aspx">System.Security.Cryptography.RNGCryptoServiceProvider</a></td></tr>
    <tr><td>Ruby</td><td><a href="http://rubydoc.info/stdlib/securerandom/1.9.2/SecureRandom">SecureRandom</a></td></tr>
    <tr><td>Python</td><td><a href="http://docs.python.org/library/os.html">os.urandom</a></td></tr>
    <tr><td>Perl</td><td><a href="http://search.cpan.org/~mkanat/Math-Random-Secure-0.06/lib/Math/Random/Secure.pm">Math::Random::Secure</a></td></tr>
    <tr><td>C/C++ (Windows API)</td><td><a href="http://en.wikipedia.org/wiki/CryptGenRandom">CryptGenRandom</a></td></tr>
    <tr><td>Any language on GNU/Linux or Unix</td><td>Read from <a href="http://en.wikipedia.org/wiki//dev/random">/dev/random</a> or /dev/urandom</td></tr>
</table> <br />

<p>
The salt needs to be unique per-user per-password. Every time a user creates an
account or changes their password, the password should be hashed using a new
random salt. Never reuse a salt.  The salt also needs to be long, so that there
are many possible salts. Make sure your salt is at least as long as the hash
function's output. The salt should be stored in the user account table alongside
the hash.
</p>

<h6>To Store a Password</h6>

<ol>
    <li>Generate a long random salt using a CSPRNG.</li>
    <li>Prepend the salt to the password and hash it with a <b>standard</b> cryptographic hash function such as SHA256.</li>
    <li>Save both the salt and the hash in the user's database record.</li>
</ol>

<h6>To Validate a Password</h6>

<ol>
    <li>Retrieve the user's salt and hash from the database.</li>
    <li>Prepend the salt to the given password and hash it using the same hash function.</li>
    <li>Compare the hash of the given password with the hash from the database. If they match, the password is correct. Otherwise, the password is incorrect.</li>
</ol><br />

<p>
    At the bottom of this page, there is an implementation of basic hashing with salt in <a href="#phpsourcecode">PHP</a> and <a href="#aspsourcecode">C#</a>.
</p>

<h4>Making Password Cracking Harder: Slow Hash Functions</h4>

<p>
    Salt ensures that attackers can't use specialized attacks like lookup tables
    and rainbow tables to crack large collections of hashes quickly, but it
    doesn't prevent them from running dictionary or brute-force attacks on each
    hash individually. High-end graphics cards (GPUs) and custom hardware can
    compute billions of hashes per second, so these attacks are still very
    effective. To make these attacks less effective, we can use a technique
    known as <b>key stretching</b>.
</p>

<p>
    The idea is to make the hash function very slow, so that even with a fast
    GPU or custom hardware, dictionary and brute-force attacks are too slow to
    be worthwhile. The goal is to make the hash function slow enough to impede
    attacks, but still fast enough to not cause a noticeable delay for the user.
</p>

<p>
    Key stretching is implemented using a special type of CPU-intensive hash
    function. Don't try to invent your own&ndash;simply iteratively hashing the
    hash of the password isn't enough as it can be parallelized in hardware and
    executed as fast as a normal hash. Use a standard algorithm like <a
    href="http://en.wikipedia.org/wiki/PBKDF2">PBKDF2</a> or <a href="http://en.wikipedia.org/wiki/Bcrypt">bcrypt</a>.
    You can find a PHP implementation of <a href="https://defuse.ca/php-pbkdf2.htm">PBKDF2 here</a>.
</p>

<p>
    These algorithms take a security factor or iteration count as an argument.
    This value determines how slow the hash function will be. For desktop
    software or smartphone apps, the best way to choose this parameter is to run
    a short benchmark on the device to find the value that makes the hash take
    about half a second. This way, your program can be as secure as possible
    without affecting the user experience.
</p>

<p>
    If you use a key stretching hash in a web application, be aware that you
    will need extra computational resources to process large volumes of
    authentication requests, and that key stretching may make it easier to run a
    Denial of Service (DoS) attack on your website.  I still recommend using key
    stretching, but with a lower iteration count. You should calculate the
    iteration count based on your computational resources and the expected
    maximum authentication request rate. Always design your system so that the
    iteration count can be increased or decreased in the future.
</p>

<p>
    If you are worried about the computational burden, but still want to use key
    stretching in a web application, consider running the key stretching
    algorithm in the user's browser with JavaScript.  The <a href="http://crypto.stanford.edu/sjcl/">Stanford JavaScript Crypto
    Library</a> includes PBKDF2. The iteration count should be set low enough
    that the system is usable with slower clients like mobile devices, and the
    system should fall back to server-side computation if the user's browser
    doesn't support JavaScript. Client-side key stretching does not remove the
    need for server-side hashing. You must hash the hash generated by the client
    the same way you would hash a normal password.
</p>

<h4>Impossible-to-crack Hashes: Keyed Hashes and Password Hashing Hardware</h4>

<p>
    As long as an attacker can use a hash to check whether a password guess is
    right or wrong, they can run a dictionary or brute-force attack on the hash.
    The next step is to add a <b>secret key</b> to the hash so that only someone
    who knows the key can use the hash validate a password. This can be
    accomplished two ways. Either the hash can be encrypted using a cipher like
    AES, or the secret key can be included in the hash using a keyed hash
    algorithm like <a href="http://en.wikipedia.org/wiki/HMAC">HMAC</a>.
</p>

<p>
    This is not as easy as it sounds. The key has to be kept secret from an
    attacker even in the event of a breach. If an attacker gains full access to
    the system, they'll be able to steal the key no matter where it is stored.
    The key must be stored in an external system, such as a physically separate
    server dedicated to password validation, or a special hardware device
    attached to the server such as the <a href="https://www.yubico.com/YubiHSM">YubiHSM</a>.
</p>

<p>
    I highly recommend this approach for any large scale (more than 100,000
    users) service. I consider it necessary for any service hosting more than
    1,000,000 user accounts.
</p>

<p>
    If you can't afford multiple dedicated servers or special hardware devices,
    you can still get some of the benefits of keyed hashes on a standard web
    server. Most databases are breached using <a
    href="http://en.wikipedia.org/wiki/SQL_injection">SQL Injection Attacks</a>,
    which, in most cases, don't give attackers access to the local filesystem
    (disable local filesystem access in your SQL server if it has this feature).
    If you generate a random key and store it in a file  that isn't accessible
    from the web, and include it into the salted hashes, then the hashes won't
    be vulnerable if your database is breached using a simple SQL injection
    attack. Don't hard-code a key into the source code, generate it randomly
    when the application is installed. This isn't as secure as using a separate
    system to do the password hashing, because if there are SQL injection
    vulnerabilities in a web application, there are probably other types, such
    as Local File Inclusion, that an attacker could use to read the secret key
    file. But, it's better than nothing.
</p>

<p>
    Please note that keyed hashes do not remove the need for salt. Clever
    attackers will eventually find ways to compromise the keys, so it is
    important that hashes are still protected by salt and key stretching.
</p>

<h3>Other Security Measures</h3>

<p>
Password hashing protects passwords in the event of a security breach. It does
not make the application as a whole more secure. Much more must be done to
prevent the password hashes (and other user data) from being stolen in the first
place.
</p>

<p>
Even experienced developers must be educated in security in order to write secure applications.
A great resource for learning about web application vulnerabilities is 
<a href="https://www.owasp.org/index.php/Main_Page">The Open Web Application
Security Project (OWASP)</a>. A good introduction is the 
<a href="http://owasptop10.googlecode.com/files/OWASP%20Top%2010%20-%202010.pdf">OWASP Top Ten Vulnerability List</a>.
Unless you understand all the vulnerabilities on the list, do not attempt to
write a web application that deals with sensitive data. It is the employer's
responsibility to ensure all developers are adequately trained in secure
application development.
</p>

<p>
Having a third party "penetration test" your application is a good idea. Even
the best programmers make mistakes, so it always makes sense to have a security
expert review the code for potential vulnerabilities. Find a trustworthy
organization (or hire staff) to review your code on a regular basis. The
security review process should begin early in an application's life and continue
throughout its development.
</p>

<p>
It is also important to monitor your website to detect a breach if one does
occur. I recommend hiring at least one person whose full time job is detecting
and responding to security breaches. If a breach goes undetected, the attacker
can make your website infect visitors with malware, so it is extremely important
that breaches are detected and responded to promptly.
</p>

<a name="faq"></a>
<h3>Frequently Asked Questions</h3>
<h4>What hash algorithm should I use?</h4>
<span style="color: green;"><b>DO</b></span> use:

<ul class="moveul">
    <li>The <a href="#phpsourcecode" title="PHP password hashing source code">PHP source code</a> or the <a href="#aspsourcecode" title="C# password hashing source code">C# source code</a> at the bottom of this page.</li>
    <li>OpenWall's <a href="http://www.openwall.com/phpass/">Portable PHP password hashing
    framework</a></li>
    <li>Any modern well-tested cryptographic hash algorithm, such as SHA256, SHA512, RipeMD, WHIRLPOOL, SHA3, etc.</li>
    <li>Well-designed key stretching algorithms such as <a href="http://en.wikipedia.org/wiki/PBKDF2">PBKDF2</a>, <a href="http://en.wikipedia.org/wiki/Bcrypt">bcrypt</a>, and <a href="http://www.tarsnap.com/scrypt.html">scrypt</a>.</li>
    <li>Secure versions of <a href="http://en.wikipedia.org/wiki/Crypt_(Unix)#Library_Function_crypt.283.29">crypt</a> ($2y$, $5$, $6$)</li>
</ul><br />
<span style="color: red;"><b>DO NOT</b></span> use:

<ul class="moveul">
    <li>Outdated hash functions like MD5 or SHA1.</li>
    <li>Insecure versions of crypt ($1$, $2$, $2a$, $2x$, $3$).</li>
    <li>Any algorithm that you designed yourself. Only use technology that is in the public domain and has been well-tested by experienced cryptographers.</li>
</ul><br />

<p>
    Even though there are no cryptographic attacks on MD5 or SHA1 that make
    their hashes easier to crack, they are old and are widely considered
    (somewhat incorrectly) to be inadequate for password storage. So I don't
    recommend using them. An exception to this rule is PBKDF2, which is
    frequently implemented using SHA1 as the underlying hash function.
</p>
<h4>How should I allow users to reset their password when they forget it?</h4>

<p>
    It is my personal opinion that all password reset mechanisms in widespread
    use today are insecure. If you have high security requirements, such as an
    encryption service would, do not let the user reset their password.
</p>

<p>
Most websites use an email loop to authenticate users who have forgotten their
password. To do this, generate a random <b>single-use</b> token that is strongly
tied to the account. Include it in a password reset link sent to the user's
email address. When the user clicks a password reset link containing a valid
token, prompt them for a new password. Be sure that the token is strongly tied
to the user account so that an attacker can't use a token sent to his own email
address to reset a different user's password.
</p>

<p>
The token should be set to expire in 15 minutes and after its first use. If not,
it can be forever used to break into the user's account. Email (SMTP) is a
plain-text protocol, and there may be malicious routers on the internet
recording email traffic. And, a user's email account (including the reset link)
may be compromised long after their password has been changed. Making the token
expire  quickly reduces the user's exposure to these attacks.
</p>

<p>
Attackers will be able to modify the tokens, so don't store the user account
information or timeout information in them. They should be an unpredictable
random binary blob used only to identify a record in a database table.
</p>

<p>
    Never send the user a new password over email.
</p>

<h4>What should I do if my user account database gets leaked/hacked?</h4>

<p>
Your first priority is to determine how the system was compromised and patch
the vulnerability the attacker used to get in. If you do not have experience
responding to breaches, I highly recommend hiring a third-party security firm.
</p>

<p>
It may be tempting to cover up the breach and hope nobody notices. However, trying to cover up a breach makes you look worse, because you're putting
your users at further risk by not informing them that their passwords and other
personal information may be
compromised. You must inform your users as soon as possible&mdash;even if you don't yet fully understand what happened.  Put a notice on the
front page of your website that links to a page with more detailed information,
and send a notice to each user by email if possible. 
</p>

<p>
Explain to your users exactly how their passwords were protected&mdash;hopefully
hashed with salt&mdash;and that even though they were protected with a salted
hash, a malicious hacker can still run dictionary and brute force attacks on the
hashes. Malicious hackers will use any passwords they find to try to login to a
user's account on a different website, hoping they used the same password on
both websites. Inform your users of this risk and recommend that they change
their password on any website or service where they used a similar password.
Force them to change their password for your service the next time they log in.
Most users will try to "change" their password to the original password to get
around the forced change quickly. Use the current password hash to ensure that
they cannot do this.
</p>

<p>
It is likely, even with salted slow hashes, that an attacker will be able to
crack some of the weak passwords very quickly. To reduce the attacker's window of opportunity to use these passwords, you should require, in
addition to the current password, an email loop for authentication until the
user has changed their password. See the previous question, "How should I allow
users to reset their password when they forget it?" for tips on implementing
email loop authentication.
</p>

<p>
Also tell your users what kind of personal information was stored on the
website. If your database includes credit card numbers, you should instruct your
users to look over their recent and future bills closely and cancel their
credit card.
</p>

<h4>What should my password policy be? Should I enforce strong passwords?</h4>
<p>
If your service doesn't have strict security requirements, then don't limit your
users. I recommend showing users information about the strength of their
password as they type it, letting them decide how secure they want their
password to be. If you have special security needs, enforce a minimum length of
12 characters and require at least two letters, two digits, and two symbols.
</p>
<p>
Do not force your users to change their password more often than once every six
months, as doing so creates "user fatigue" and makes users less likely to choose
good passwords. Instead, train users to change their password whenever they feel
it has been compromised, and to never tell their password to anyone. If it is a
business setting, encourage employees to use paid time to memorize and practice
their password.
</p>

<h4>If an attacker has access to my database, can't they just replace the hash of my password with their own hash and login?</h4>

<p>
Yes, but if someone has accesss to your database, they probably already have
access to everything on your server, so they wouldn't need to login to your
account to get what they want. The purpose of password hashing (in the context
of a website) is not to protect the website from being breached, but to protect
the passwords if a breach does occur.
</p>

<p>
You can prevent hashes from being replaced during a SQL injection attack by
connecting to the database with two users with different permissions. One for
the 'create account' code and one for the 'login' code. The 'create account'
code should be able to read and write to the user table, but the 'login' code
should only be able to read.
</p>

<h4>Why bother hashing?</h4>

<p>
Your users are entering their password into your website. They are trusting you
with their security. If your database gets hacked, and your users' passwords are
unprotected, then malicious hackers can use those passwords to compromise your
users' accounts on other websites and services (most people use the same
password everywhere). It's not just your security that's at risk, it's your
users'. You are responsible for your users' security.
</p>

<a name="phpsourcecode"></a>
<h3>PHP Password Hashing Code</h3>

The following is a secure implementation of salted hashing in PHP. If you want
to use PBKDF2 in PHP, use <a href="https://defuse.ca/php-pbkdf2.htm">Defuse Cyber-Security's implementation</a>.
<br /><br />

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
