<h1>How to Store Passwords</h1>

<p>
This article will walk you through the design of a secure password storage
system. Along the way, we'll encounter many common mistakes, and for each
mistake, we'll understand why it's a mistake and how to fix it.
</p>

<div style="background-color: #FFCCCC; border: solid red 1px; padding: 10px;">
<strong>IMPORTANT WARNING:</strong> If you are thinking of writing your own
password hashing code, <strong>please don't!</strong>. It's too easy to screw
up. No, that cryptography course you took in university doesn't make you exempt
from this warning. This applies to everyone: <strong>DO NOT WRITE YOUR OWN
CRYPTO!</strong> The problem of storing passwords has already been solved. Use either use either <a
href="http://www.openwall.com/phpass/">phpass</a> or the source code given on
this page.
</div>

<p>
If for some reason you missed that big red warning note, please go read it now.
Really, this guide is <b>not</b> meant to walk you through the process of
writing your own storage system, it's to explain the reasons why passwords
should be stored a certain way.
</p>

<p>
With that in mind, we can begin. To understand password storage, we will start
with an insecure system and iteratively improve it until it is secure. At each
iteration, we'll see how an attacker can take advantage of the vulnerability,
and how we as defenders can make the attacker's job harder.
</p>

<p>
For concreteness, we'll use a standard web site login system as an
example. 
</p>

<h2>Weak System #1: Plain Text Storage</h2>

<p>
The most obvious way to store passwords is to just put them straight into the
database without any kind of encryption or hashing. Obviously, this is
a horrible idea, since if an attacker gains access to your database, they will
have all of your users' passwords, and you'll be in PR hell trying to win back
your users' trust.
</p>

<p>
Worse, if attackers know you're storing passwords in plain text, they will
target you, because password databases are valuable and can be sold on the black
market.
</p>

<p>
It's a huge risk, not just to you, but to your users too. It's very common for
people to re-use the same password on multiple websites. If your website exposes
a user's password to an attacker, that attacker might be able to use it to log
in to the user's account on another website. You might be tempted to blame the
user for re-using their password, but if you had protected the passwords, the
user wouldn't be at risk, so it <em>is</em> partly your responsibility.
</p>

<p>
Another problem you'll have, if you store passwords in plain text, is that when
you get hacked, it will be nearly impossible to give your users a secure way to
reclaim their account, even after you've fixed the vulnerability. Once the
attacker has all of the passwords, they can log in to any accounts they're
interested in, set new passwords, and have permanent access to the accounts.
Protecting the password database buys you a little bit of time to tell your
users that they need to change their password.
</p>

<h2>Weak System #2: Encryption</h2>

<p>
The next obvious step is to encrypt passwords with symmetric encryption. As
we'll see, this turns out to be a bad idea.
</p>

<p>
Symmetric encryption works by using a random key to encrypt some data. The
encrypted data is called the ciphertext. To turn the ciphertext back into the
original data, you need to know the key that it was encrypted with. Without the
key, you can't decrypt the ciphertext.
</p>

<p>
You might think encrypting passwords would be a good idea. It's not, because
where do you store the key? The server that creates user accounts and verifies
usernames and passwords has to have access to it. So, chances are, if an
attacker can get the encrypted password database, they'll be able to get the
encryption key, and will be able to decrypt all of the passwords.
</p>

<h2>Weak System #3: Hashing Without Salt</h2>

<p>
To move on to a more secure design, we need to realize that to verify
a password, you don't actually need to know the correct password. It is possible
to compute the "fingerprint" of a password, with the following properties:
</p>

<ul>
    <li>It's very unlikely for two different passwords to have the same fingerprint.</li>
    <li>It's hard to "reverse" the fingerprint back into the password.</li>
</ul>

<p>
This can be done with a cryptographic hash function like SHA256. These functions
compute a fixed-length fingerprint from a variable-length input. They have the
properties we want: It's hard to find two inputs that hash to the same value,
and given an output, it's very difficult to find the input.
</p>

<p>
Here are some example SHA256 hashes. You can see that even if the input only
changes by one letter, the output looks completely different.
</p>

<div class="passcrack" style="text-align: center;">
hash("hello") = 2cf24dba5fb0a30e26e83b2ac5b9e29e1b161e5c1fa7425e73043362938b9824<br />
hash("hbllo") = 58756879c05c68dfac9866712fad6a93f8146f337a69afe7dd238f3364946366<br />
hash("waltz") = c0e81794384491161f1777c232bc6bd9ec38f616560b120fda8e90f383853542<br />
</div>

<p>
We can use a function like this to protect passwords. Instead of storing the
password in plain text, or encrypting the password, we can store the hash of the
password. Then, when a user logs in, we hash the password they've given us and
compare it to the hash that's saved in the database. Because the chance of two
passwords producing the same hash is extremely low (one of the properties of
a hash function), the chance of someone getting in with the wrong password is
also extremely low.
</p>

<p>
You might think we can stop here. In fact, we can't, because storing passwords
this way is still very weak. 
</p>

<p>
To see why, consider what happens when two users have the same password: the
hashes are the same! An attacker can tell, just by comparing the hashes, which
users are using the same password. Clearly this is a vulnerability, since if the
attacker wants to get in to Alice's account, and sees that Bob has the same
password, the attacker can bribe (or torture) Bob for <em>his</em> password to
get into <em>Alice's</em> account.
</p>

<p>
That's not the only reason. Another reason is that the same password always
hashes to the same value. There's a one-to-one correspondence between hashes and
passwords. This means that an attacker can <b>pre-compute</b> huge tables of
hashes, then search for the hash they want to crack in that table. Because the
search can be done <a href="https://en.wikipedia.org/wiki/Binary_search_algorithm">very quickly</a>, cracking hashes this way is a lot faster than
trying to guess the password for each hash.
</p>

<p>
To see how fast it can be, copy and paste these SHA256 hashes into <a href="https://crackstation.net/">CrackStation's Hash Cracker</a>:
</p>

<div class="passcrack" style="text-align: center;">
c11083b4b0a7743af748c85d343dfee9fbb8b2576c05f3a7f0d632b0926aadfc<br />
08eac03b80adc33dc7d8fbe44b7c7b05d3a2c511166bdb43fcb710b03ba919e7<br />
e4ba5cbd251c98e6cd1c23f126a3b81d8d8328abc95387229850952b3ef9f904<br />
5206b8b8a996cf5320cb12ca91c7b790fba9f030408efe83ebb83548dc3007bd<br />
</div>

<p>
The result is that all four hashes can be cracked in under a second. This is
obviously much faster than trying to guess each hash's password one by one.
Using this technique, an attacker can crack most of the hashes in your user
account database in a matter of minutes.
</p>

<p>
These password cracking databases are very real, and are used by attackers all
the time. One special type, called a "<a href="https://en.wikipedia.org/wiki/Rainbow_table">Rainbow Table</a>", can fit the MD5 hashes of
all possible 8 character passwords into a <a
href="https://www.freerainbowtables.com/en/tables2/">1TB file</a> that can be
downloaded from the Internet.
</p>

<h2>Weak System #4: Hashing With Salt</h2>

<p>
The attacks we saw in the previous section were possible because every time the
same password was hashed, the result was the same. This let attackers see who
was using the same password, and let them build a huge database of hashes that
could be quickly searched to find the password for a given hash.
</p>

<p>
To prevent these attacks, we need to make sure that even if two users use the
same password, or if one user uses the same password twice, the hash values are
always different. This is done by adding some randomness to the hashing process.
</p>

<pre>
    - Prepend random salt to thwart above attacks.
    - Mistakes: short salt, salt reuse, not generating salt with a CSPRNG
    - Not good enough because attackers with ASICs and GPUs can still test
      passwords reallly fast.
</pre>

<h2>Secure System #1: Slow Hashing</h2>

<pre>
     - Use slow hash function.
     - PBKDF2, scrypt, bcrypt (anything else?)
     - Weak passwords can still be found .. intro next section with
       security-by-obscurity key.

- Important to keep this at a high level, similar to how block ciphers are
explained before getting into the details. We can point readers to the
scrypt/Catena papers and PHC if they want to find out how these things are
really implemented. We do need to mention some desirable properties, including
memory hardness, etc. We want to get people interested in how they work, but we
don't want them to think they can design one themselves. (see solardiz email)

</pre>

<h2>Increasing Security: Hardware Security Modules</h2>

<pre>
    - Use hardware device with embedded key to do the hashing, so that unless
      it's physically stolen and tampered with, the passwords are really safe.
    - Also possible to do w/o custom hardware... just set up dedicated password
      authentication box that does nothing but hash passwords; no services, etc.
</pre>

<h2>Frequently Asked Questions</h2>

<pre>
    - Basically the same list of FAQ from the original article except emphasize
        slow hashing.
</pre>

<h2>Source Code</h2>

<pre>
    - Embed the source code here.
    - Putting the source code way down here might make people miss it, so add
      prominent links up at the top (not just in the red warning box).
</pre>
