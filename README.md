# ttf2pscid2
TTF to PostScript Type 2 CIDFont converter for GhostScript

Converts TTF to a Type 2 CIDFont with 2-byte Unicode CMap encoding, for embedding into PostScript directly as CMap and CIDFont dictionaries, not as Adobe conformant CID-keyed font files.

The TrueType font is wrapped in PostScript syntax as sfnts binary data as for Type 42 base fonts. The CIDMap is compacted to reduce file size and command line options offer further compression. Subsetting is supported.
OpenType/TTF is supported but OpenType/CFF is not yet.

Basic Multilingual Plane only.

Depends on https://github.com/scriptituk/postscript-procs files.
Tested on GhostScript v8.7 to latest.
