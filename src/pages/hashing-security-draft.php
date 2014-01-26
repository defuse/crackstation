<h1>How to Store Passwords</h1>

<p>
This article will walk you through the design of a secure password hashing
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
writing your own hashing system, it's to explain the theory behind why password
hashing should be done a certain way.
</p>

<p>
With that in mind, we can begin. To understand password storage, we will start
with an insecure system and iteratively improve it until it is secure. At each
iteration, we'll see how an attacker can take advantage of the vulnerability,
and how we as defenders can make the attacker's job harder.
</p>

<p>
For concreteness, we'll use a standard web application login system as an
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
user wouldn't be at risk, so it <em>is</em> your responsibility.
</p>

<p>
Another problem you'll have, if you store passwords in plain text, is that when
you get hacked, it will be impossible to give your users a secure way to reclaim
their account, even after you've fixed the vulnerability. Once the attacker has
all of the passwords, they can log in to any accounts they're interested in, set
new passwords, and have permanent access to them.
</p>

<h2>Weak System #2: Encryption</h2>

<p>
The next obvious step is to encrypt passwords with symmetric encryption.
</p>

<pre>
    - Where do you store the key.
    - If attackers get the key, they get all the passwords.
    - Notice that you don't really need to know the password to verify it.
</pre>

<h2>Weak System #3: Hashing Without Salt</h2>

<pre>
    - Lookup tables, Reverse lookup tables, rainbow tables, etc.
    - Stay within the context of web app for this (i.e. apply attacks to the
      entire hash database dump, for simplicity).
</pre>

<h2>Weak System #4: Hashing With Salt</h2>

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

<h2>Secure System #2: Keyed Hashes</h2>

<pre>
    - Security by obscurity hide-the-key-somewhere-and-hope-attacker-doesn't-find-it.
    - Why this and not encrypt (first weak one)? - Now we have a robust slow hash system to fall back on if the 
      attacker DOES find the key.
    - Should I even include this section? I really don't think it would be
     useful in practice (attacker can just add an echo to the hash code to get the
      key)
    - I think this is sometimes called hashing with "pepper"
</pre>

<h2>Secure System #3: Hardware Security Module</h2>

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
