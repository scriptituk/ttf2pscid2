# ttf2pscid2
## TTF to PostScript Type 2 CIDFont Converter

### Description

This Ghostscript-run script converts TTF to Type 2 CIDFont with 2-byte Unicode CMap encoding, for embedding into PostScript directly as CMap and CIDFont PostScript dictionaries.

PostScript is an excellent language for parsing binary file formats efficiently. This is a pure PostScript program but uses Ghostscript switches for convenience to pass in parameters. Other and better TTF to PS converters exist, notably [FontForge scripting](https://fontforge.github.io/scripting.html), [ttftotype42](https://github.com/kohler/lcdf-typetools) (maintained) and [ttftot42](https://github.com/nih-at/ttftot42) (dead), but the simple solution here only requires Ghostscript to run without any compilation. Being a Unicode CID-keyed font, rendered text strings are just 2 byte UTF-16 for which UTF-8 conversion is included.

The TrueType font is wrapped in PostScript syntax as `sfnts` binary data as for Type 42 base fonts. The CIDMap is compacted to reduce file size and command line options offer further compression.

Subsetting is supported. OpenType/TTF is supported but not OpenType/CFF as yet nor TrueType Collections. Vertical writing mode is not yet supported.

Basic Multilingual Plane only; surrogate pairs for supplementary characters show as `.notdef`.  
Depends on [pslutils](https://github.com/scriptituk/pslutils) files string.ps, file.ps, sort.ps & math.ps.  
Tested on GhostScript v8.7 to v9.22.

### Usage:

* `gs -dQUIET -dNODISPLAY -dBATCH -dNOPAUSE <options> ttf2pscid2.ps`

or simply  

* `gs -q -o- -dNODISPLAY <options> ttf2pscid2.ps`

e.g.

* `gs -q -o- -dNODISPLAY -sttf=arial.ttf ttf2pscid2.ps`  
  converts arial.ttf to arial.t42, the .t42 extension indicates Type 42 derived PostScript code

then insert the generated code into a PostScript file and use it as normal, e.g.:
* `/ArialMT 10 selectfont` to scale and select the font.

Options are set using Ghostscript parameter switches (`-d` for definitions and `-s` for strings)
* `-sttf=file` and `-sttf=dir/` sets the TTF file(s) to convert  
  a trailing slash enumerates all files with .ttf or .otf extensions in the specified directory
* `-st42=file` and `-st42=dir/` sets the output filename or directory  
  the default output directory is the same as the TTF
* `-dpsname` sets the output basename to the PostScript font name contained in the TTF `name` table
* `-doptimise` remaps the glyph index numbering to produce a more compact CIDMap and hence smaller output file
* `-ssubset=chars` subsets the font to the given UTF-8 characters and sets `-doptimise`
* `-ducs2` interprets the subset characters as 16 bit UCS-2 (UTF-16BE BMP) instead of UTF-8
* `-dbinary` saves `sfnts` as binary data instead of ASCII hexadecimal
* `-dcompress` saves long `sfnts` strings as zlib/deflate compressed binary data instead of uncompressed and sets `-dbinary`
* `-dcomments` comments the `sfnts` strings for debugging
* `-dinfo` outputs tab-separated font information to the gs output file specified by -sOutputFile= or -o
* `-sinc=dir` sets the include path for [pslutils](https://github.com/scriptituk/pslutils) dependencies
* `-sjson=string` decodes the given JSON encoded object of key/value pairs defining the required options, which take precedence

### Examples:

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
* `gs -q -o- -dNODISPLAY -sttf=arial.ttf -ssubset='Fee: €25 (£22)' ttf2pscid2.ps`  
  subsets arial.ttf to contain UTF-8 characters ' ():25Fe£€' only (10 CIDs)
* `gs -q -o info.txt -dNODISPLAY -sttf=arialbd.ttf -dinfo ttf2pscid2.ps`  
  writes font information for arialbd.ttf to info.txt, e.g. (tabs shown as |):  
  `family|filename|fullname|issymbolfont|notice|psname|style|subfamily|trademark|uniqueid|version`  
 ` Arial|arialbd.ttf|Arial Bold|false|© 2008 The Monotype Corporation.|Arial-BoldMT|Bold|Bold|Arial is a  trademark…|Monotype:Arial Bold v5.06|5.06`
* `gs -q -o- -dNODISPLAY -sjson='{"ttf":"arial.ttf","subset":"Fee: €25 (£22)","compress":true}' ttf2pscid2.ps`  
  is JSON equivalent to  
  `gs -q -o- -dNODISPLAY -sttf=arial.ttf -ssubset='Fee: €25 (£22)' -dcompress ttf2pscid2.ps`

### Sample

`gs -q -o- -dNODISPLAY -sttf=Marlborough.ttf -ssubset='Hello World' -dcomments ttf2pscid2.ps`

produces…

```postscript
%!PS-TrueTypeFont

/cidmap1 {exch string exch {dup 0 get exch dup length 1 sub 1 exch getinterval 0
3 1 roll {dup 0 lt {2 index 1 add exch neg 1 sub 3 index add}{dup} ifelse 4 -1
roll pop 3 1 roll 1 3 index {3 index exch 2 index exch put 1 add} for} forall
pop pop} forall} bind def

/CIDInit /ProcSet findresource begin
	10 dict begin
		begincmap
		/CMapType 1 def
		/CMapName /Identity-H def
		/CIDSystemInfo << /Registry (Adobe) /Ordering (Identity) /Supplement 0 >> def
		1 begincodespacerange
			<0000> <ffff>
		endcodespacerange
		0 usefont
		1 begincidrange
			<0000> <ffff> 0
		endcidrange
		endcmap
		currentdict CMapName exch /CMap defineresource pop
	end
end

/cidfont <<
	/CIDCount 115
	/CIDFontName /Marlborough
	/CIDFontType 2
	/CIDMap [] readonly
	/CIDSystemInfo <<
		/Ordering (Identity)
		/Registry (Adobe)
		/Supplement 0
	>> readonly
	/CharStrings <<
		/.notdef 0
	>> readonly
	/Encoding [] readonly
	/FontBBox [-111 -483 2179 1970] readonly
	/FontInfo <<
		/FamilyName (Marlborough)
		/FullName (Marlborough)
		/ItalicAngle 0.0
		/Notice ((c) 1989 iifonts)
		/UnderlinePosition -150
		/UnderlineThickness 100
		/Weight (Regular)
		/extra <<
			/Tascent 1481
			/isSymbolFont false
			/lineSpacing 2453
			/style (Regular)
			/unitsPerEm 2048
		>> readonly
		/isFixedPitch false
		/version (1.0)
	>> readonly
	/FontMatrix [1 0 0 1 0 0] readonly
	/FontType 42
	/GDBytes 1
	/PaintType 0
	/sfnts [] readonly
>> def

cidfont dup /CIDMap exch /CIDCount get [
	[32 3]
	[72 4]
	[87 5]
	[100 6 -2]
	[108 8]
	[111 9]
	[114 10]
] cidmap1 readonly put

cidfont /sfnts [
% directory
<000100000006004000020020676C796690038E250000006C0000022468656164
67254ACD0000029000000036686865610FC80B6B000002C800000024686D7478
25F60282000002EC0000002C6C6F63610202027A00000318000000186D617870
002C02A6000003300000002000>
% glyf table
%	gid=4 cid=72 H
<000100850000036205C9000C000033113311211133112311211123859801AE97
97FE529805C9FD85027BFA3702C1FD3F00>
%	gid=5 cid=87 W
<00010027000004CD05C9001F000021230333131E01173E01371333131E01173E
013713330323032E01270E010703019888E98B87111002061811B279B2121806
021010888BEA87CB090C03071008C205C9FC8F6FA34855AF5A036DFC935AAF55
4C9E700371FA3704272D64374F7828FC0000000E00>
%	gid=6 cid=100 d
<0002004CFFE702CB05C90011001F00002123350E012322021110123332161711
331127112E012322061514163332363702CB8E306A368D94948A386A318E8E2B
593059515159305B29623C3F011D0110010D011E3C3C0202FA37F002524443CB
DDEACF444400002400>
%	gid=7 cid=101 e
<0002004CFFE502C3043F0016001F000001330E01232202101233321615140607
211E0133323637012135342623220607023486099E84A59E9EA5999B0102FE1C
01545E4C5405FEA901544D57545606015CB2C50110023B010FF6F5222210E0BC
7F79011B299C89A2AC00009300>
%	gid=8 cid=108 l
<0001007B0000010A05C90004000033113311237B8F8F05C9FA37002700>
%	gid=9 cid=111 o
<00020048FFE502C7043F000B0017000013141633323610262322061523101233
3212100223220211D9515F595151595F51919EA59F9D9D9FA59E0212EAC4CC01
C4CFC6EB011E010FFEECFDCFFEEB0110011D005F00>
%	gid=10 cid=114 r
<0001007B0000021B043D00120000331133153E01333216331522262322060711
237B8F36744805150505150550752D8F04277B4C450289025056FCF200>
% head table
<0001000000000000D905350E5F0F3CF500000800000000000000000000000000
00000000FF91FE1D088307B200000009000000000000000000>
% hhea table
<0001000007B201E3000009D7FF91FFA508830001000000000000000000000000
0000000B00>
% hmtx table
%	hMetrics
<04CD000004CD000004CD000001B2000003E7008504F400270348004C030C004C
0185007B030E0048021B007B00>
%	leftSideBearing
% loca table
<0000000000000000000000180052008600BC00CA00F4011200>
% maxp table
<00010000000B02A4002000000000000100000000000000000000000000000001
00>
] readonly put

cidfont dup /CIDFontName get exch /CIDFont defineresource
/CIDFontName get /Identity-H [2 index] composefont pop
```

then emit UTF-16 text like…

```postscript
/Marlborough 100 selectfont
100 100 moveto
(\0H\0e\0l\0l\0o\0 \0W\0o\0r\0l\0d) show
showpage
```

Easy!
