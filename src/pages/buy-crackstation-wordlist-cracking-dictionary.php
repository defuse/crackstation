<h1>CrackStation's Password Cracking Dictionary</h1>

<p>
I am releasing CrackStation's main password cracking dictionary (1,493,677,782
words, 15GB) for download.
</p>

<h2>What's in the list?</h2>

<p>
The list contains every wordlist, dictionary, and password database leak that
I could find on the internet (and I spent a LOT of time looking). It also
contains every word in the Wikipedia databases (pages-articles, retrieved 2010,
all languages) as well as lots of books from <a
href="http://www.gutenberg.org/">Project Gutenberg</a>. It also includes the
passwords from some low-profile database breaches that were being sold in the
underground years ago.
</p>

<p>
The format of the list is a standard text file sorted in non-case-sensitive
alphabetical order. Lines are separated with a newline "\n" character.
</p>

<p>
You can test the list without downloading it by giving SHA256 hashes to the <a
href="/">free hash cracker</a> or to <a href="https://twitter.com/plzcrack">@PlzCrack</a> on twitter. Here's a <a
href="https://defuse.ca/checksums.htm"> tool for computing hashes easily</a>.
Here are the results of cracking <a
href="https://defuse.ca/blog/cracking-linkedin-hashes-with-crackstation">LinkedIn's</a>
and <a
href="https://defuse.ca/blog/cracking-eharmonys-unsalted-hashes-with-crackstation">
eHarmony's</a> password hash leaks with the list. 
</p>

<p>
The list is responsible for
cracking about 30% of all hashes given to CrackStation's free hash cracker, but
that figure should be taken with a grain of salt because some people try hashes
of really weak passwords just to test the service, and others try to crack their
hashes with other online hash crackers before finding CrackStation. Using the
list, we were able to crack 49.98% of one customer's set of 373,000
human password hashes to motivate their move to a better salting scheme.
</p>

<h2>Step 1: Pay what you want.</h2>

<p>
The wordlist is being sold using a "pay what you want" model. That means
you can pay absolutely any amount of money you want for the wordlist. Even
nothing. Use the PayPal donate button, Bitcoin address, or Litecoin address
below to make your payment.
</p>

<p>
<strong>How much should I pay?</strong>
</p>

<p>
Think about the following points when deciding how much to pay:
</p>

<ul>
    <li>
        If I wasn't doing a "pay what want" I would set the price at
        $5.
    </li>
    <li>
        The money will be used for open source security research and development projects.
    </li>
    <li>
        It took about 3 weeks of full-time work to make this dictionary
        (searching, downloading, scripting, processing).
    </li>
    <li>
        I will not be offended by small payments.
    </li>
    <li>
        If you have no money or don't want to pay, seeding the torrents and
        sharing this page with your friends is appreciated!
    </li>
</ul>

<br />
<br />
<center>
<form action="https://www.paypal.com/cgi-bin/webscr" method="post">
<input type="hidden" name="cmd" value="_s-xclick" />
<input type="hidden" name="hosted_button_id" value="PNMQT9EL48KBA" />
<input type="image" src="https://www.paypalobjects.com/en_US/i/btn/btn_donateCC_LG.gif" border="0" name="submit" alt="PayPal - The safer, easier way to pay online!" />
<img alt="" border="0" src="https://www.paypalobjects.com/en_US/i/scr/pixel.gif" width="1" height="1" />
</form>
</center>

<div style="text-align: center; font-family: monospace;">
    <br />
    Bitcoin: 1G6PjrYhy5uXPsY1DxirEA3Ahx5EHn5W45<br /><br />
    Litecoin: Lg46hm5DY5QT2TU7rUN5yHuGZ2PHqjUb9L
</div>

<h2>Step 2: Download!</h2>

<p>
<b>Note:</b> To download the torrents, you will need a torrent client like
Transmission (for Linux and Mac), or uTorrent for Windows.
</p>

<div style="text-align: center;">
    <a class="wldl" href="/downloads/crackstation.txt.gz.torrent">
        <span style="font-size: 20pt;">Torrent (Fast)</span>
        <br />
        GZIP-compressed (level 9). 4.2 GiB compressed. 15 GiB uncompressed.
    </a>
    <br /><br />
    <a class="wldl" href="/files/crackstation.txt.gz">
        <span style="font-size: 16pt;">HTTP Mirror (Slow)</span>
    </a>
</div>

<h4>Checksums (crackstation.txt.gz)</h4>

<pre>
MD5:    4748a72706ff934a17662446862ca4f8
SHA1:   efa3f5ecbfba03df523418a70871ec59757b6d3f
SHA256: a6dc17d27d0a34f57c989741acdd485b8aee45a6e9796daf8c9435370dc61612
</pre>

<a name="humanpasswords"></a>
<h3>Smaller Wordlist (Human Passwords Only)</h3>

<p>
I got some requests for a wordlist with just the "real human" passwords leaked
from various website databases. This smaller list contains just those passwords.
There are about 64 million passwords in this list!
</p>

<div style="text-align: center;">
    <a class="wldl" href="/downloads/crackstation-human-only.txt.gz.torrent">
        <span style="font-size: 20pt;">Torrent (Fast)</span>
        <br />
        GZIP-compressed. 247 MiB compressed. 684 MiB uncompressed.
    </a>
    <br /><br />
    <a class="wldl" href="/files/crackstation-human-only.txt.gz">
        <span style="font-size: 16pt;">HTTP Mirror (Slow)</span>
    </a>
    <br />
</div>

<h4>Checksums (crackstation-human-only.txt.gz)</h4>

<pre>
MD5:    fbc3ca43230086857aac9b71b588a574
SHA1:   116c5f60b50e80681842b5716be23951925e5ad3
SHA256: 201f8815c71a47d39775304aa422a505fc4cca18493cfaf5a76e608a72920267
</pre>

<h3>Sharing and Licensing</h3>

<div style="text-align: center;">
<a rel="license" href="http://creativecommons.org/licenses/by-sa/3.0/deed.en_US"><img alt="Creative Commons License" style="border-width:0" src="/images/cc-by-sa-big.png" /></a>.
</div>

<p>
You <em>are</em> allowed to share these lists! They are both licensed under
the <a href="http://creativecommons.org/licenses/by-sa/3.0/deed.en_US">Creative
Commons Attribution-ShareAlike 3.0</a> license. If you do share them, I would
appreciate it if you included a link to this page.
</p>

