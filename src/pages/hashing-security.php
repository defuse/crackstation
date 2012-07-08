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
</tr>
<tr>
    <td>4. <a href="#ineffective" title="The wrong way to do password hashing">Ineffective Hashing Methods</a></td>
    <td>5. <a href="#properhashing" title="The right way to do password hashing, with salt">How to hash properly</a></td>
    <td>6. <a href="#faq" title="Frequently asked questions about password hashing and salt">Frequently Asked Questions</a></td>
</tr>
<tr>
    <td>7. <a href="#phpsourcecode" title="PHP password hashing example source code">PHP Source Code</a></td>
    <td>8. <a href="#javasourcecode" title="Java password hashing example source code">Java Source Code</a></td>
    <td>9. <a href="#aspsourcecode" title="C# password hashing example source code">ASP.NET (C#) Source Code</a></td>
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
    <li>If the hashes match, the user is granted access. If not, the user is told they entered invalid login credentials.</li>
    <li>Steps 3 and 4 repeat everytime someone tries to login to their account.</li>
</ol>
<br />

<p>
In step 4, never tell the user if it was the username or password they got wrong. Always display
a generic message like "Invalid username or password." This prevents attackers from enumerating
valid usernames without knowing their passwords.
</p>

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
    <tr><td>Java</td><td><a href="http://docs.oracle.com/javase/6/docs/api/java/security/SecureRandom.html">java.security.SecureRandom</a></td></tr>
    <tr><td>Dot NET (C#, VB)</td><td><a href="http://msdn.microsoft.com/en-us/library/system.security.cryptography.rngcryptoserviceprovider.aspx">System.Security.Cryptography.RNGCryptoServiceProvider</a></td></tr>
    <tr><td>Ruby</td><td><a href="http://rubydoc.info/stdlib/securerandom/1.9.3/SecureRandom">SecureRandom</a></td></tr>
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

<a name="othersecurity"></a>
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
    <li>The <a href="#phpsourcecode" title="PHP password hashing source code">PHP source code</a>,
            <a href="#javasourcecode" title="Java password hashing source code">Java source code</a>,
            or the <a href="#aspsourcecode" title="C# password hashing source code">C# source code</a>
            at the bottom of this page.
    </li>
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
The token must be set to expire in 15 minutes or after it is used, whichever
comes first. It is also a good idea to expire any existing password tokens when
the user logs in (they remembered their password) or requests another reset
token. If a token doesn't expire, it can be forever used to break into the
user's account.  Email (SMTP) is a plain-text protocol, and there may be
malicious routers on the internet recording email traffic. And, a user's email
account (including the reset link) may be compromised long after their password
has been changed.  Making the token expire as soon as possible reduces the
user's exposure to these attacks.
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

<h4>Why do I have to use a special algorithm like HMAC? Why can't I just append
the password to the secret key?</h4>

<p> Hash functions like MD5, SHA1, and SHA2 use the <a href="http://en.wikipedia.org/wiki/Merkle%E2%80%93Damg%C3%A5rd_construction">
Merkle–Damgård construction</a>, which makes them vulnerable to what are known
as length extension attacks. This means that given a hash H(X), an attacker can
find the value of H(pad(X) + Y), for any other string Y, without knowing X.
pad(X) is the padding function used by the hash.
</p>

<p>
This means that given a hash H(key + message), an attacker can compute H(pad(key +
message) + extension), without knowing the key. If the hash was being used as a
message authentication code, using the key to prevent an attacker from being
able to modify the message and replace it with a different valid hash, the
system has failed, since the attacker now has a valid hash of message +
extension.
</p>

<p>
It is not clear how an attacker could use this attack to crack a password hash
quicker.  However, because of the attack, it is considered bad practice to
use a plain hash function for keyed hashing. A clever cryptographer may one day
come up with a clever way to use these attacks to make cracking faster, so use
HMAC.
</p>


<h4>Should the salt come before or after the password?</h4>

<p>
It doesn't matter, but pick one and stick with it for interoperability's sake.
Having the salt come before the password seems to be more common.
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
<h3>PHP PBKDF2 Password Hashing Code</h3>

The following code is a secure implementation of PBKDF2 hashing in PHP. You can find a test suite
and benchmark code for it on <a href="https://defuse.ca/php-pbkdf2.htm">Defuse Cyber-Security's
PBKDF2 for PHP</a> page. The code is in the public domain, so you may use it for any purpose
whatsoever.
<br /><br />

<div class="passcrack">
&lt;?php<br />
/*<br />
&nbsp;* Password hashing with PBKDF2.<br />
&nbsp;* Author: havoc AT defuse.ca<br />
&nbsp;* www: https://defuse.ca/php-pbkdf2.htm<br />
&nbsp;*/<br />
<br />
// These constants may be changed without breaking existing hashes.<br />
define(&quot;PBKDF2_HASH_ALGORITHM&quot;, &quot;sha256&quot;);<br />
define(&quot;PBKDF2_ITERATIONS&quot;, 1000);<br />
define(&quot;PBKDF2_SALT_BYTES&quot;, 24);<br />
define(&quot;PBKDF2_HASH_BYTES&quot;, 24);<br />
<br />
define(&quot;HASH_SECTIONS&quot;, 4);<br />
define(&quot;HASH_ALGORITHM_INDEX&quot;, 0);<br />
define(&quot;HASH_ITERATION_INDEX&quot;, 1);<br />
define(&quot;HASH_SALT_INDEX&quot;, 2);<br />
define(&quot;HASH_PBKDF2_INDEX&quot;, 3);<br />
<br />
function create_hash($password)<br />
{<br />
&nbsp;&nbsp; &nbsp;// format: algorithm:iterations:salt:hash<br />
&nbsp;&nbsp; &nbsp;$salt = base64_encode(mcrypt_create_iv(PBKDF2_SALT_BYTES, MCRYPT_DEV_URANDOM));<br />
&nbsp;&nbsp; &nbsp;return PBKDF2_HASH_ALGORITHM . &quot;:&quot; . PBKDF2_ITERATIONS . &quot;:&quot; . &nbsp;$salt . &quot;:&quot; . <br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp;base64_encode(pbkdf2(<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;PBKDF2_HASH_ALGORITHM,<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;$password,<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;$salt,<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;PBKDF2_ITERATIONS,<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;PBKDF2_HASH_BYTES,<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;true<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp;));<br />
}<br />
<br />
function validate_password($password, $good_hash)<br />
{<br />
&nbsp;&nbsp; &nbsp;$params = explode(&quot;:&quot;, $good_hash);<br />
&nbsp;&nbsp; &nbsp;if(count($params) &lt; HASH_SECTIONS)<br />
&nbsp;&nbsp; &nbsp; &nbsp; return false; <br />
&nbsp;&nbsp; &nbsp;$pbkdf2 = base64_decode($params[HASH_PBKDF2_INDEX]);<br />
&nbsp;&nbsp; &nbsp;return slow_equals(<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp;$pbkdf2,<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp;pbkdf2(<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;$params[HASH_ALGORITHM_INDEX],<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;$password,<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;$params[HASH_SALT_INDEX],<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;(int)$params[HASH_ITERATION_INDEX],<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;strlen($pbkdf2),<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;true<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp;)<br />
&nbsp;&nbsp; &nbsp;);<br />
}<br />
<br />
// Compares two strings $a and $b in length-constant time.<br />
function slow_equals($a, $b)<br />
{<br />
&nbsp;&nbsp; &nbsp;$diff = strlen($a) ^ strlen($b);<br />
&nbsp;&nbsp; &nbsp;for($i = 0; $i &lt; strlen($a) &amp;&amp; $i &lt; strlen($b); $i++)<br />
&nbsp;&nbsp; &nbsp;{<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp;$diff |= ord($a[$i]) ^ ord($b[$i]);<br />
&nbsp;&nbsp; &nbsp;}<br />
&nbsp;&nbsp; &nbsp;return $diff === 0; <br />
}<br />
<br />
/*<br />
&nbsp;* PBKDF2 key derivation function as defined by RSA&#039;s PKCS #5: https://www.ietf.org/rfc/rfc2898.txt<br />
&nbsp;* $algorithm - The hash algorithm to use. Recommended: SHA256<br />
&nbsp;* $password - The password.<br />
&nbsp;* $salt - A salt that is unique to the password.<br />
&nbsp;* $count - Iteration count. Higher is better, but slower. Recommended: At least 1000.<br />
&nbsp;* $key_length - The length of the derived key in bytes.<br />
&nbsp;* $raw_output - If true, the key is returned in raw binary format. Hex encoded otherwise.<br />
&nbsp;* Returns: A $key_length-byte key derived from the password and salt.<br />
&nbsp;*<br />
&nbsp;* Test vectors can be found here: https://www.ietf.org/rfc/rfc6070.txt<br />
&nbsp;*<br />
&nbsp;* This implementation of PBKDF2 was originally created by https://defuse.ca<br />
&nbsp;* With improvements by http://www.variations-of-shadow.com<br />
&nbsp;*/<br />
function pbkdf2($algorithm, $password, $salt, $count, $key_length, $raw_output = false)<br />
{<br />
&nbsp;&nbsp; &nbsp;$algorithm = strtolower($algorithm);<br />
&nbsp;&nbsp; &nbsp;if(!in_array($algorithm, hash_algos(), true))<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp;die(&#039;PBKDF2 ERROR: Invalid hash algorithm.&#039;);<br />
&nbsp;&nbsp; &nbsp;if($count &lt;= 0 || $key_length &lt;= 0)<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp;die(&#039;PBKDF2 ERROR: Invalid parameters.&#039;);<br />
<br />
&nbsp;&nbsp; &nbsp;$hash_length = strlen(hash($algorithm, &quot;&quot;, true));<br />
&nbsp;&nbsp; &nbsp;$block_count = ceil($key_length / $hash_length);<br />
<br />
&nbsp;&nbsp; &nbsp;$output = &quot;&quot;;<br />
&nbsp;&nbsp; &nbsp;for($i = 1; $i &lt;= $block_count; $i++) {<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp;// $i encoded as 4 bytes, big endian.<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp;$last = $salt . pack(&quot;N&quot;, $i);<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp;// first iteration<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp;$last = $xorsum = hash_hmac($algorithm, $last, $password, true);<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp;// perform the other $count - 1 iterations<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp;for ($j = 1; $j &lt; $count; $j++) {<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;$xorsum ^= ($last = hash_hmac($algorithm, $last, $password, true));<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp;}<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp;$output .= $xorsum;<br />
&nbsp;&nbsp; &nbsp;}<br />
<br />
&nbsp;&nbsp; &nbsp;if($raw_output)<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp;return substr($output, 0, $key_length);<br />
&nbsp;&nbsp; &nbsp;else<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp;return bin2hex(substr($output, 0, $key_length));<br />
}<br />
?&gt;
</div>

<a name="javasourcecode"></a>
<h3>Java PBKDF2 Password Hashing Code</h3>
<p>
    The following code is a secure implementation of PBKDF2 hashing in Java.
    The code is in the public domain, so you may use it for any purpose whatsoever.
</p>
<div class="passcrack">
import java.security.SecureRandom;<br />
import javax.crypto.spec.PBEKeySpec;<br />
import javax.crypto.SecretKeyFactory;<br />
import java.math.BigInteger;<br />
import java.security.NoSuchAlgorithmException;<br />
import java.security.spec.InvalidKeySpecException;<br />
<br />
/*<br />
&nbsp;* PBKDF2 salted password hashing.<br />
&nbsp;* Author: havoc AT defuse.ca<br />
&nbsp;* www: http://crackstation.net/hashing-security.htm<br />
&nbsp;*/<br />
public class PasswordHash<br />
{<br />
&nbsp;&nbsp; &nbsp;public static final String PBKDF2_ALGORITHM = &quot;PBKDF2WithHmacSHA1&quot;;<br />
<br />
&nbsp;&nbsp; &nbsp;// The following constants may be changed without breaking existing hashes.<br />
&nbsp;&nbsp; &nbsp;public static final int SALT_BYTES = 24;<br />
&nbsp;&nbsp; &nbsp;public static final int HASH_BYTES = 24;<br />
&nbsp;&nbsp; &nbsp;public static final int PBKDF2_ITERATIONS = 1000;<br />
<br />
&nbsp;&nbsp; &nbsp;public static final int ITERATION_INDEX = 0;<br />
&nbsp;&nbsp; &nbsp;public static final int SALT_INDEX = 1;<br />
&nbsp;&nbsp; &nbsp;public static final int PBKDF2_INDEX = 2;<br />
<br />
&nbsp;&nbsp; &nbsp;/**<br />
&nbsp;&nbsp; &nbsp; * Returns a salted PBKDF2 hash of the password.<br />
&nbsp;&nbsp; &nbsp; *<br />
&nbsp;&nbsp; &nbsp; * @param &nbsp; password &nbsp; &nbsp;the password to hash<br />
&nbsp;&nbsp; &nbsp; * @return &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;a salted PBKDF2 hash of the password<br />
&nbsp;&nbsp; &nbsp; */<br />
&nbsp;&nbsp; &nbsp;public static String createHash(String password)<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp;throws NoSuchAlgorithmException, InvalidKeySpecException<br />
&nbsp;&nbsp; &nbsp;{<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp;return createHash(password.toCharArray());<br />
&nbsp;&nbsp; &nbsp;}<br />
<br />
&nbsp;&nbsp; &nbsp;/**<br />
&nbsp;&nbsp; &nbsp; * Returns a salted PBKDF2 hash of the password.<br />
&nbsp;&nbsp; &nbsp; *<br />
&nbsp;&nbsp; &nbsp; * @param &nbsp; password &nbsp; &nbsp;the password to hash<br />
&nbsp;&nbsp; &nbsp; * @return &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;a salted PBKDF2 hash of the password<br />
&nbsp;&nbsp; &nbsp; */<br />
&nbsp;&nbsp; &nbsp;public static String createHash(char[] password)<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp;throws NoSuchAlgorithmException, InvalidKeySpecException<br />
&nbsp;&nbsp; &nbsp;{<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp;// Generate a random salt<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp;SecureRandom random = new SecureRandom();<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp;byte[] salt = new byte[SALT_BYTES];<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp;random.nextBytes(salt);<br />
<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp;// Hash the password<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp;byte[] hash = pbkdf2(password, salt, PBKDF2_ITERATIONS, HASH_BYTES);<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp;// format iterations:salt:hash<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp;return PBKDF2_ITERATIONS + &quot;:&quot; + toHex(salt) + &quot;:&quot; + &nbsp;toHex(hash);<br />
&nbsp;&nbsp; &nbsp;}<br />
<br />
&nbsp;&nbsp; &nbsp;/**<br />
&nbsp;&nbsp; &nbsp; * Validates a password using a hash.<br />
&nbsp;&nbsp; &nbsp; *<br />
&nbsp;&nbsp; &nbsp; * @param &nbsp; password &nbsp; &nbsp;the password to check<br />
&nbsp;&nbsp; &nbsp; * @param &nbsp; goodHash &nbsp; &nbsp;the hash of the valid password<br />
&nbsp;&nbsp; &nbsp; * @return &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;true if the password is correct, false if not<br />
&nbsp;&nbsp; &nbsp; */<br />
&nbsp;&nbsp; &nbsp;public static boolean validatePassword(String password, String goodHash)<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp;throws NoSuchAlgorithmException, InvalidKeySpecException<br />
&nbsp;&nbsp; &nbsp;{<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp;return validatePassword(password.toCharArray(), goodHash);<br />
&nbsp;&nbsp; &nbsp;}<br />
<br />
&nbsp;&nbsp; &nbsp;/**<br />
&nbsp;&nbsp; &nbsp; * Validates a password using a hash.<br />
&nbsp;&nbsp; &nbsp; *<br />
&nbsp;&nbsp; &nbsp; * @param &nbsp; password &nbsp; &nbsp;the password to check<br />
&nbsp;&nbsp; &nbsp; * @param &nbsp; goodHash &nbsp; &nbsp;the hash of the valid password<br />
&nbsp;&nbsp; &nbsp; * @return &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;true if the password is correct, false if not<br />
&nbsp;&nbsp; &nbsp; */<br />
&nbsp;&nbsp; &nbsp;public static boolean validatePassword(char[] password, String goodHash)<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp;throws NoSuchAlgorithmException, InvalidKeySpecException<br />
&nbsp;&nbsp; &nbsp;{<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp;// Decode the hash into its parameters<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp;String[] params = goodHash.split(&quot;:&quot;);<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp;int iterations = Integer.parseInt(params[ITERATION_INDEX]);<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp;byte[] salt = fromHex(params[SALT_INDEX]);<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp;byte[] hash = fromHex(params[PBKDF2_INDEX]);<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp;// Compute the hash of the provided password, using the same salt, <br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp;// iteration count, and hash length<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp;byte[] testHash = pbkdf2(password, salt, iterations, hash.length);<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp;// Compare the hashes in constant time. The password is correct if<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp;// both hashes match.<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp;return slowEquals(hash, testHash);<br />
&nbsp;&nbsp; &nbsp;}<br />
<br />
&nbsp;&nbsp; &nbsp;/**<br />
&nbsp;&nbsp; &nbsp; * Compares two byte arrays in length-constant time. This comparison method<br />
&nbsp;&nbsp; &nbsp; * is used so that password hashes cannot be extracted from an on-line <br />
&nbsp;&nbsp; &nbsp; * system using a timing attack and then attacked off-line.<br />
&nbsp;&nbsp; &nbsp; * <br />
&nbsp;&nbsp; &nbsp; * @param &nbsp; a &nbsp; &nbsp; &nbsp; the first byte array<br />
&nbsp;&nbsp; &nbsp; * @param &nbsp; b &nbsp; &nbsp; &nbsp; the second byte array <br />
&nbsp;&nbsp; &nbsp; * @return &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;true if both byte arrays are the same, false if not<br />
&nbsp;&nbsp; &nbsp; */<br />
&nbsp;&nbsp; &nbsp;private static boolean slowEquals(byte[] a, byte[] b)<br />
&nbsp;&nbsp; &nbsp;{<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp;int diff = a.length ^ b.length;<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp;for(int i = 0; i &lt; a.length &amp;&amp; i &lt; b.length; i++)<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;diff |= a[i] ^ b[i];<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp;return diff == 0;<br />
&nbsp;&nbsp; &nbsp;}<br />
<br />
&nbsp;&nbsp; &nbsp;/**<br />
&nbsp;&nbsp; &nbsp; * &nbsp;Computes the PBKDF2 hash of a password.<br />
&nbsp;&nbsp; &nbsp; *<br />
&nbsp;&nbsp; &nbsp; * @param &nbsp; password &nbsp; &nbsp;the password to hash.<br />
&nbsp;&nbsp; &nbsp; * @param &nbsp; salt &nbsp; &nbsp; &nbsp; &nbsp;the salt<br />
&nbsp;&nbsp; &nbsp; * @param &nbsp; iterations &nbsp;the iteration count (slowness factor)<br />
&nbsp;&nbsp; &nbsp; * @param &nbsp; bytes &nbsp; &nbsp; &nbsp; the length of the hash to compute in bytes<br />
&nbsp;&nbsp; &nbsp; * @return &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;the PBDKF2 hash of the password<br />
&nbsp;&nbsp; &nbsp; */<br />
&nbsp;&nbsp; &nbsp;private static byte[] pbkdf2(char[] password, byte[] salt, int iterations, int bytes)<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp;throws NoSuchAlgorithmException, InvalidKeySpecException<br />
&nbsp;&nbsp; &nbsp;{<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp;PBEKeySpec spec = new PBEKeySpec(password, salt, iterations, bytes * 8);<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp;SecretKeyFactory skf = SecretKeyFactory.getInstance(PBKDF2_ALGORITHM);<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp;return skf.generateSecret(spec).getEncoded();<br />
&nbsp;&nbsp; &nbsp;}<br />
<br />
&nbsp;&nbsp; &nbsp;/**<br />
&nbsp;&nbsp; &nbsp; * Converts a string of hexadecimal characters into a byte array.<br />
&nbsp;&nbsp; &nbsp; *<br />
&nbsp;&nbsp; &nbsp; * @param &nbsp; hex &nbsp; &nbsp; &nbsp; &nbsp; the hex string<br />
&nbsp;&nbsp; &nbsp; * @return &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;the hex string decoded into a byte array<br />
&nbsp;&nbsp; &nbsp; */<br />
&nbsp;&nbsp; &nbsp;private static byte[] fromHex(String hex)<br />
&nbsp;&nbsp; &nbsp;{<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp;byte[] binary = new byte[hex.length() / 2];<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp;for(int i = 0; i &lt; binary.length; i++)<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp;{<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;binary[i] = (byte)Integer.parseInt(hex.substring(2*i, 2*i+2), 16);<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp;}<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp;return binary;<br />
&nbsp;&nbsp; &nbsp;}<br />
<br />
&nbsp;&nbsp; &nbsp;/**<br />
&nbsp;&nbsp; &nbsp; * Converts a byte array into a hexadecimal string.<br />
&nbsp;&nbsp; &nbsp; *<br />
&nbsp;&nbsp; &nbsp; * @param &nbsp; array &nbsp; &nbsp; &nbsp; the byte array to convert<br />
&nbsp;&nbsp; &nbsp; * @return &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;a length*2 character string encoding the byte array<br />
&nbsp;&nbsp; &nbsp; */<br />
&nbsp;&nbsp; &nbsp;private static String toHex(byte[] array)<br />
&nbsp;&nbsp; &nbsp;{<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp;BigInteger bi = new BigInteger(1, array);<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp;String hex = bi.toString(16);<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp;int paddingLength = (array.length * 2) - hex.length();<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp;if(paddingLength &gt; 0) <br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;return String.format(&quot;%0&quot; + paddingLength + &quot;d&quot;, 0) + hex;<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp;else<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;return hex;<br />
&nbsp;&nbsp; &nbsp;}<br />
<br />
&nbsp;&nbsp; &nbsp;/**<br />
&nbsp;&nbsp; &nbsp; * Tests the basic functionality of the PasswordHash class<br />
&nbsp;&nbsp; &nbsp; *<br />
&nbsp;&nbsp; &nbsp; * @param &nbsp; args &nbsp; &nbsp; &nbsp; &nbsp;ignored<br />
&nbsp;&nbsp; &nbsp; */<br />
&nbsp;&nbsp; &nbsp;public static void main(String[] args)<br />
&nbsp;&nbsp; &nbsp;{<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp;try<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp;{<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;// Print out 10 hashes<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;for(int i = 0; i &lt; 10; i++)<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;System.out.println(PasswordHash.createHash(&quot;p\r\nassw0Rd!&quot;));<br />
<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;// Test password validation<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;boolean failure = false;<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;System.out.println(&quot;Running tests...&quot;);<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;for(int i = 0; i &lt; 100; i++)<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;{<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;String password = &quot;&quot;+i;<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;String hash = createHash(password);<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;String secondHash = createHash(password);<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;if(hash.equals(secondHash)) {<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;System.out.println(&quot;FAILURE: TWO HASHES ARE EQUAL!&quot;);<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;failure = true;<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;}<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;String wrongPassword = &quot;&quot;+(i+1);<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;if(validatePassword(wrongPassword, hash)) {<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;System.out.println(&quot;FAILURE: WRONG PASSWORD ACCEPTED!&quot;);<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;failure = true;<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;}<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;if(!validatePassword(password, hash)) {<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;System.out.println(&quot;FAILURE: GOOD PASSWORD NOT ACCEPTED!&quot;);<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;failure = true;<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;}<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;}<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;if(failure)<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;System.out.println(&quot;TESTS FAILED!&quot;);<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;else<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;System.out.println(&quot;TESTS PASSED!&quot;);<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp;}<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp;catch(Exception ex)<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp;{<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;System.out.println(&quot;ERROR: &quot; + ex);<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp;}<br />
&nbsp;&nbsp; &nbsp;}<br />
<br />
}<br />

</div>

			<a name="aspsourcecode"></a>
<h3>ASP.NET (C#) Password Hashing Code</h3>
<p>
The following code is a secure implementation of salted hashing in C# for ASP.NET. It is in the
public domain, so you may use it for any purpose whatsoever.
</p>
<div class="passcrack">
using System;<br />
using System.Text;<br />
using System.Security.Cryptography;<br />
<br />
namespace PasswordHash<br />
{<br />
&nbsp;&nbsp; &nbsp;/// &lt;summary&gt;<br />
&nbsp;&nbsp; &nbsp;/// Salted password hashing with PBKDF2-SHA1.<br />
&nbsp;&nbsp; &nbsp;/// Author: havoc AT defuse.ca<br />
&nbsp;&nbsp; &nbsp;/// www: http://crackstation.net/hashing-security.htm<br />
&nbsp;&nbsp; &nbsp;/// Compatibility: .NET 3.0 and later.<br />
&nbsp;&nbsp; &nbsp;/// &lt;/summary&gt;<br />
&nbsp;&nbsp; &nbsp;class PasswordHash<br />
&nbsp;&nbsp; &nbsp;{<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp;// The following constants may be changed without breaking existing hashes.<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp;public const int SALT_BYTES = 24;<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp;public const int HASH_BYTES = 24;<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp;public const int PBKDF2_ITERATIONS = 1000;<br />
<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp;public const int ITERATION_INDEX = 0;<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp;public const int SALT_INDEX = 1;<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp;public const int PBKDF2_INDEX = 2;<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; <br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp;/// &lt;summary&gt;<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp;/// Creates a salted PBKDF2 hash of the password.<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp;/// &lt;/summary&gt;<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp;/// &lt;param name=&quot;password&quot;&gt;The password to hash.&lt;/param&gt;<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp;/// &lt;returns&gt;The hash of the password.&lt;/returns&gt;<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp;public static string CreateHash(string password)<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp;{<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;// Generate a random salt<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;RNGCryptoServiceProvider csprng = new RNGCryptoServiceProvider();<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;byte[] salt = new byte[SALT_BYTES];<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;csprng.GetBytes(salt);<br />
<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;// Hash the password and encode the parameters<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;byte[] hash = PBKDF2(password, salt, PBKDF2_ITERATIONS, HASH_BYTES);<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;return PBKDF2_ITERATIONS + &quot;:&quot; + <br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;Convert.ToBase64String(salt) + &quot;:&quot; + <br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;Convert.ToBase64String(hash);<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp;}<br />
<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp;/// &lt;summary&gt;<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp;/// Validates a password given a hash of the correct one.<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp;/// &lt;/summary&gt;<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp;/// &lt;param name=&quot;password&quot;&gt;The password to check.&lt;/param&gt;<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp;/// &lt;param name=&quot;goodHash&quot;&gt;A hash of the correct password.&lt;/param&gt;<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp;/// &lt;returns&gt;True if the password is correct. False otherwise.&lt;/returns&gt;<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp;public static bool ValidatePassword(string password, string goodHash)<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp;{<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;// Extract the parameters from the hash<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;char[] delimiter = { &#039;:&#039; };<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;string[] split = goodHash.Split(delimiter);<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;int iterations = Int32.Parse(split[ITERATION_INDEX]);<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;byte[] salt = Convert.FromBase64String(split[SALT_INDEX]);<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;byte[] hash = Convert.FromBase64String(split[PBKDF2_INDEX]);<br />
<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;byte[] testHash = PBKDF2(password, salt, iterations, hash.Length);<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;return SlowEquals(hash, testHash);<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp;}<br />
<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp;/// &lt;summary&gt;<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp;/// Compares two byte arrays in length-constant time. This comparison<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp;/// method is used so that password hashes cannot be extracted from <br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp;/// on-line systems using a timing attack and then attacked off-line.<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp;/// &lt;/summary&gt;<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp;/// &lt;param name=&quot;a&quot;&gt;The first byte array.&lt;/param&gt;<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp;/// &lt;param name=&quot;b&quot;&gt;The second byte array.&lt;/param&gt;<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp;/// &lt;returns&gt;True if both byte arrays are equal. False otherwise.&lt;/returns&gt;<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp;private static bool SlowEquals(byte[] a, byte[] b)<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp;{<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;uint diff = (uint)a.Length ^ (uint)b.Length;<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;for (int i = 0; i &lt; a.Length &amp;&amp; i &lt; b.Length; i++)<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;diff |= (uint)(a[i] ^ b[i]);<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;return diff == 0;<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp;}<br />
<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp;/// &lt;summary&gt;<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp;/// Computes the PBKDF2-SHA1 hash of a password.<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp;/// &lt;/summary&gt;<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp;/// &lt;param name=&quot;password&quot;&gt;The password to hash.&lt;/param&gt;<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp;/// &lt;param name=&quot;salt&quot;&gt;The salt.&lt;/param&gt;<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp;/// &lt;param name=&quot;iterations&quot;&gt;The PBKDF2 iteration count.&lt;/param&gt;<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp;/// &lt;param name=&quot;outputBytes&quot;&gt;The length of the hash to generate, in bytes.&lt;/param&gt;<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp;/// &lt;returns&gt;A hash of the password.&lt;/returns&gt;<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp;private static byte[] PBKDF2(string password, byte[] salt, int iterations, int outputBytes)<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp;{<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;Rfc2898DeriveBytes pbkdf2 = new Rfc2898DeriveBytes(password, salt);<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;pbkdf2.IterationCount = iterations;<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;return pbkdf2.GetBytes(outputBytes);<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp;}<br />
&nbsp;&nbsp; &nbsp;}<br />
}
</div>

</div> <!-- body stuff -->
<br />
<div style="text-align: center;">
    <h4>Article and code written by <a href="https://defuse.ca/">Defuse Cyber-Security.</a></h4>
</div>

</div>
