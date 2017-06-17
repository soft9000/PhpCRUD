PHP CRUD enjoys an extremely simple design.

Having a simple design means that PHP CRUD
is very easy to use, yet it works extremely 
well.

The public test case is a good place to start
to see how things work. That test case is 
located in `index.php`.

NOTES
=====
The only area to be mindful of is defining 
your minimum record length: While ANY size 
data can be added to your data file, for 
reasons of safety any subsequent update()s 
of a record must be constrained by your 
minimum-record length.

In general, I would personally consider 
using some multiple of the power of 2. 
128, 256, 1024 (etc.) Note that 4096 
is the default caching multiple for 
most modern servers.

The update()able buffer size is managed 
by: 

IndexedDataInfo::data_min = 1024;

Finally, Base64 encoding is OFF by 
default (run the test case to see why :)
To enable encoding, set:

IndexedDataInfo::isEncoded = true;

Finally, note that when we would like to
save / recall arrays, that the IndexedArray
Class - as added 2013/02/25 - will now make 
that a lot easier.


Enjoy,

-Randall Nagy

President, Soft9000.com
http://Soft9000.com


"The File System IS a Database" -Randall Nagy


