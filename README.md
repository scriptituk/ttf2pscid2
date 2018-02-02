# ttf2pscid2
### TTF to PostScript Type 2 CIDFont converter

This Ghostscript-run script converts TTF to a Type 2 CIDFont with 2-byte Unicode CMap encoding, for embedding into PostScript directly as CMap and CIDFont dictionaries, not as Adobe conformant CID-keyed font files.

PostScript is an excellent language for parsing binary file formats efficiently. This is a pure PostScript program but uses Ghostscript switches for convenience to pass in parameters. There are other and better TTF to PS converters around, notably [FontForge scripting](https://fontforge.github.io/scripting.html), [ttftotype42](https://github.com/kohler/lcdf-typetools) (maintained) and [ttftot42](https://github.com/nih-at/ttftot42) (dead), but the simple solution here only requires Ghostscript to run, no compilation.

The TrueType font is wrapped in PostScript syntax as sfnts binary data as for Type 42 base fonts. The CIDMap is compacted to reduce file size and command line options offer further compression.

Subsetting is supported. OpenType/TTF is supported but not OpenType/CFF as yet nor TrueType Collections. Vertical writing mode is not yet supported.

Basic Multilingual Plane only.  
Depends on [postscript-procs](https://github.com/scriptituk/postscript-procs) files string.ps, file.ps, sort.ps & math.ps.  
Tested on GhostScript v8.7 to v9.22.

Usage:
* `gs -dQUIET -dNODISPLAY -dBATCH -dNOPAUSE <options> -sttf=<ttf> ttf2pscid2.ps`

or simply  

* `gs -q -o- -dNODISPLAY <options> -sttf=<ttf> ttf2pscid2.ps`

e.g.

* `gs -q -o- -dNODISPLAY -sttf=stencil.ttf ttf2pscid2.ps`  
  converts arial.ttf to arial.t42, the .t42 extension indicates Type 42 derived PostScript code

then insert the generated code into your PostScript file and use  
* `/ArialMT 10 selectfont (Hello) show` or similar to scale and set the current font and Paint glyphs for 'Hello'.

Options are set using Ghostscript parameter switches (`-d` for definitions and `-s` for strings)
* `-sttf=file` and `-sttf=dir/` sets the TTF file(s) to convert  
  a trailing slash enumerates all files with .ttf or .otf extensions in the specified directory
* `-st42=file` and `-st42=dir/` sets the output filename or directory  
  the default output directory is the same as the TTF
* `-sinc=dir` sets the include path for [postscript-procs](https://github.com/scriptituk/postscript-procs) dependencies
* `-dpsname` sets the output basename to the PostScript font name contained in the TTF `name` table
* `-doptimise` remaps the glyph index numbering to produce a more compact CIDMap and hence smaller output file
* `-ssubset=chars` subsets the font to the given UTF-8 characters and sets `-doptimise`
* `-ducs2` interprets the subset characters as 16 bit UCS-2 (UTF-16BE BMP) instead of UTF-8
* `-dbinary` saves sfnts as binary data instead of ASCII hexadecimal
* `-dcompress` saves long sfnts strings as zlib/deflate compressed binary data instead of uncompressed and sets `-dbinary`
* `-dcomments` comments the sfnts strings for debugging
* `-dinfo` outputs tab-separated font information to the gs output file specified by -sOutputFile= or -o
* `-sargs=file` runs the given PostScript file of dictionary key/value pairs defining the required options, which take precedence

Examples:
* `gs -q -o- -dNODISPLAY -sttf=times.ttf -st42=times.ps ttf2pscid2.ps`  
  converts times.ttf to times.ps
* `gs -q -o- -dNODISPLAY -sttf=here/ttf/ -st42=there/t42/ ttf2pscid2.ps`  
  converts all TTFs in here/ttf/ to .t42 files in there/t42/ much faster than converting singly
* `gs -q -o- -dNODISPLAY -sttf=/src/arial.ttf -st42=/dest/ -sinc=/bin/inc/ /bin/ttf2pscid2.ps`  
  converts /src/arial.ttf saving PostScript file in /dest/ setting include path to /bin/inc/
* `gs -q -o- -dNODISPLAY -sttf=arial.ttf -dpsname ttf2pscid2.ps`  
  converts arial.ttf to say ArialMT.t42 where ArialMT is the PostScript font name given in arial.ttf
* `gs -q -o- -dNODISPLAY -sttf=arial.ttf -doptimise -dcompress ttf2pscid2.ps`  
  compacts and compresses to produce the smallest output file
* `gs -q -o- -dNODISPLAY -sttf=arial.ttf -dsubset='Fee: €25 (£22)' ttf2pscid2.ps`  
  subsets arial.ttf to contain UTF-8 characters ' ():25Fe£€' only (10 CIDs)
* `gs -q -o info.txt -dNODISPLAY -sttf=arialbd.ttf -dinfo ttf2pscid2.ps`  
  writes font information for arialbd.ttf to info.txt, e.g. (tabs shown as |):  
  `family|filename|fullname|issymbolfont|notice|psname|style|subfamily|trademark|uniqueid|version`  
 ` Arial|arialbd.ttf|Arial Bold|false|© 2008 The Monotype Corporation.|Arial-BoldMT|Bold|Bold|Arial is a  trademark…|Monotype:Arial Bold v5.06|5.06`
* `gs -q -o- -dNODISPLAY -sargs=args.def ttf2pscid2.ps`  
  reads the options from file args.def (in valid PostScript syntax), e.g.:  
  `/subset (Fee: €25 (£22))`  
  `/psname true`  
  `/optimise true`  
  `/compress true`  
  `/ttf (arial.ttf)`
  
