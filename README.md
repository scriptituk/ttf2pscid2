# ttf2pscid2
## TTF to PostScript Type 2 CIDFont Converter

### Description

This Ghostscript-run script converts TTF to Type 2 CIDFont with 2-byte Unicode CMap encoding, for embedding into PostScript directly as CMap and CIDFont PostScript dictionaries.

PostScript is an excellent language for parsing binary file formats efficiently. This is a pure PostScript program but uses Ghostscript switches for convenience to pass in parameters. Other and better TTF to PS converters exist, notably [FontForge scripting](https://fontforge.github.io/scripting.html), [ttftotype42](https://github.com/kohler/lcdf-typetools) (maintained) and [ttftot42](https://github.com/nih-at/ttftot42) (dead), but the simple solution here only requires Ghostscript to run, no compilation is involved. Being a Unicode CID-keyed font, rendered text strings are just 2 byte UTF-16 for which conversion from UTF-8 is included.

The TrueType font is wrapped in PostScript syntax as `sfnts` binary data as for Type 42 base fonts. The CIDMap is compacted to reduce file size and command line options offer further compression.

Subsetting is supported. OpenType/TTF is supported but not OpenType/CFF as yet nor TrueType Collections. Vertical writing mode is not supported.

Basic Multilingual Plane only; surrogate pairs for supplementary characters show as `.notdef`.  
Depends on [pslutils](https://github.com/scriptituk/pslutils) files string.ps, file.ps, sort.ps & math.ps.  
Tested on GhostScript v8.7 to v9.27.

### Usage:

* `gs -dQUIET -dNODISPLAY -dBATCH -dNOPAUSE <options> ttf2pscid2.ps`

or simply  

* `gs -q -o- -dNODISPLAY <options> ttf2pscid2.ps`

or (recommended)

* `gs -q -o- -dNODISPLAY -- ttf2pscid2.ps '(JSON options)'`

e.g.

* `gs -q -o- -dNODISPLAY -sttf=arial.ttf ttf2pscid2.ps`  
  converts arial.ttf to arial.t42, the .t42 extension indicates Type 42 derived PostScript code

then insert the generated code into a PostScript file and use it as normal, e.g.:
* `/ArialMT 10 selectfont` to scale and select ArialMT as the font parameter in the graphics state.

Options may be set using Ghostscript parameter switches (`-d` for definitions and `-s` for strings):
* `-sttf=file` and `-sttf=dir/` sets the TTF file(s) to convert  
  a trailing slash enumerates all files with .ttf or .otf extensions in the specified directory
* `-st42=file` and `-st42=dir/` sets the output filename or directory  
  the default output directory is the TTF directory
* `-dpsname` sets the output basename to the PostScript font name contained in the TTF `name` table
* `-doptimise` remaps the glyph index numbering to produce a more compact CIDMap and hence smaller output file
* `-ssubset=chars` subsets the font to the given UTF-8 characters and sets `-doptimise`
* `-ducs2` interprets the subset characters as 16 bit UCS-2 (UTF-16BE BMP) instead of UTF-8
* `-dbinary` saves `sfnts` as binary data instead of ASCII hexadecimal
* `-dcompress` saves long `sfnts` strings as zlib/deflate compressed binary data instead of uncompressed and sets `-dbinary`
* `-dcomments` comments the `sfnts` strings for debugging
* `-sinc=dir` sets the include path for the [pslutils](https://github.com/scriptituk/pslutils) dependencies
* `-dinfo` outputs tab-separated font information to the gs output file specified by -sOutputFile= or -o (- for stdout)  
  no conversion will be done if this option is given – it is for gathering font information only

Options may also be set by a JSON-encoded object of key/value pairs, passed as a PostScript string token (in parenthesis) after the script filename (Ghostscript must be called with the `--` command line option).
JSON-supplied options take precedence over parameter switches.
The advantage of JSON is that UTF gets converted to an ASCII-compatible representation as required by PostScript.
Note that PostScript string literals require the three special characters ( ) \ to be backslash-escaped (balanced pairs of parentheses need not be) and non-ASCII characters converted to octal escape sequences.
This PostScript-escaping must be done on the JSON-encoded string and on any non-ASCII subset string.
Final shell-escaping is also advisable.

Note that GhostScript versions since 9.23 use double-quotes to protect whitespace in parameters, therefore they get stripped out even if escaped, which is surely the shell’s job (but I suppose this is non-standard usage).
So to pass in " use the \042 octal escape sequence.
This is always necessary for JSON options.

The PHP utilities file utils.php shows how to do it in PHP.

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
* `gs -q -o- -dNODISPLAY -- ttf2pscid2.ps '({"ttf":"arial.ttf","subset":"Fee: €25 \(£22\)","compress":true})'`  
  is the JSON equivalent of  
  `gs -q -o- -dNODISPLAY -sttf=arial.ttf -ssubset='Fee: €25 (£22)' -dcompress ttf2pscid2.ps`  
  but as noted above gs strips quotes in parameters, so do this (see also utils.php):  
```bash
  json='({"ttf":"arial.ttf","subset":"Fee: €25 \(£22\)","compress":true})'
  args=`sed 's/"/\\\\042/g' <<< "$json"`
  gs -q -o- -dNODISPLAY -- ttf2pscid2.ps "$args"
```

### Sample

`gs -q -o- -dNODISPLAY -sttf=Marlborough.ttf -ssubset='Olá mundo' -dcomments ttf2pscid2.ps`

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
    /CIDCount 226
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
    [79 4]
    [100 5]
    [108 6 -4]
    [117 10]
    [225 11]
] cidmap1 readonly put

cidfont /sfnts [
% table directory
<000100000006004000020020676C796690ECC5F50000006C000002B868656164
67254ACD0000032400000036686865610FC80B6C0000035C00000024686D7478
2AC8032400000380000000306C6F6361034C0270000003B00000001A6D617870
002D02A6000003CC0000002000>
% glyf table
%   gid=4 cid=79 (O)
<00020054FFE3038505E500090016000005220210122012100223112202111012
3332121110022301ECCACECE0193D0D0C980787682807777801D018702F70184
FE7BFD0AFE790579FEE0FECCFE9FFEC8012C014A0148012F00>
%   gid=5 cid=100 (d)
<0002004CFFE702CB05C90011001F00002123350E012322021110123332161711
331127112E012322061514163332363702CB8E306A368D94948A386A318E8E2B
593059515159305B29623C3F011D0110010D011E3C3C0202FA37F002524443CB
DDEACF444400002400>
%   gid=6 cid=108 (l)
<0001007B0000010A05C90004000033113311237B8F8F05C9FA37002700>
%   gid=7 cid=109 (m)
<0001007F0000049E044200220000331133153E013216173E0133321615112311
34262322060711231134262322060711237F8D40788960204979426865903440
34622F8F354034632E8D04276F484245494C42888BFCD1030070554547FCC703
0070554547FCC70100>
%   gid=8 cid=110 (n)
<0001007F000002D5044200140000331133153E01333216151123113426232206
0711237F8D40784468658D35423667288D04276F4842888BFCD1030070554943
FCC7008200>
%   gid=9 cid=111 (o)
<00020048FFE502C7043F000B0017000013141633323610262322061523101233
3212100223220211D9515F595151595F51919EA59F9D9D9FA59E0212EAC4CC01
C4CFC6EB011E010FFEECFDCFFEEB0110011D005F00>
%   gid=10 cid=117 (u)
<0001007BFFE502D1042700140000011123350E01232226351133111416333236
37113302D18D40784468658D35423666298D0427FBD96F4842868B0331FD0070
55494303390000FE00>
%   gid=11 cid=225 (\000\341) <00E1>
<00030048FFF602DF060C000A0029002E0000010E011514163332363719010E01
232226353424373D01342623220607273E013332161511141617232701133301
23023DABBD503D317139357A44788A0100F54A554950138A199D7EA091090991
11FEEAD1B2FEE76A023B26A66F3E513B370158FE253436907C9CE73E182B6558
5D6C16939AA6BCFE1B407A3C60044E015EFEA2D700>
% head table
<0001000000000000CAF8C0B25F0F3CF500000800000000000000000000000000
00000000FF91FE1D088307B200000009000000000000000000>
% hhea table
<0001000007B201E3000009D7FF91FFA508830001000000000000000000000000
0000000C00>
% hmtx table
%   hMetrics
<04CD000004CD000004CD000001B2000003D900540348004C0185007B0517007F
034E007F030E0048034E007B0348004800>
%   leftSideBearing
% loca table
<00000000000000000000002C0060006E00A200C400EE0112015C000000>
% maxp table
<00010000000C02A4002000000000000100000000000000000000000000000001
00>
] readonly put

cidfont dup /CIDFontName get exch /CIDFont defineresource
/CIDFontName get /Identity-H [2 index] composefont pop
```

then emit UTF-16 text for ‘Olá mundo’ like this:

```postscript
/Marlborough 100 selectfont
100 100 moveto
(\0O\0l\0\341\0 \0m\0u\0n\0d\0o) show
showpage
```

or emit UTF-8 text for ‘Olá mundo’ like this:

```postscript
% include the code for utf8toutf16be and int2str16 from file string.ps in repository pslutils,
% or include this krunch:
/utf8toutf16be {[exch 0 exch {exch dup 0 eq {pop dup 16#BF le {16#7F and 0}{dup
16#DF le {16#1F and 1}{dup 16#EF le {16#0F and 2}{16#07 and 103} ifelse} ifelse}
ifelse}{1 sub 3 1 roll 16#3F and exch 6 bitshift or exch dup 100 eq {pop
16#10000 sub dup -10 bitshift 16#3FF and 16#D800 or exch 16#3FF and 16#DC00 or
0} if} ifelse} forall pop] dup length 1 bitshift string exch 0 exch {3 copy -8
bitshift put exch 1 add exch 3 copy 16#FF and put pop 1 add} forall pop} bind def

/Marlborough 100 selectfont
100 100 moveto
(Ol\303\241 mundo) utf8toutf16be show
showpage
```

Easy!
Tested with [Microsoft YaHei TTF](https://docs.microsoft.com/en-us/typography/font-list/microsoft-yahei) with 29425 glyphs in Latin, Cyrillic, Greek, Turkish, Chinese (微软雅黑).
