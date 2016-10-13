Moose
=====

L[oose] object [M]apper - it maps data on objects always failing gracefully.
 Instead of throwing an exception when some piece of data isn't right it will
 just return to you a stack of collected errors and a partial object.

This behavior is useful for consuming 3d-party APIs or building your own
 where you need to report all invalid pieces of data.
