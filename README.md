# ttf2pscid2
### TTF to PostScript Type 2 CIDFont Converter

#### Description

This Ghostscript-run script converts TTF to Type 2 CIDFont with 2-byte Unicode CMap encoding, for embedding into PostScript directly as CMap and CIDFont PostScript dictionaries.

PostScript is an excellent language for parsing binary file formats efficiently. This is a pure PostScript program but uses Ghostscript switches for convenience to pass in parameters. Other and better TTF to PS converters exist, notably [FontForge scripting](https://fontforge.github.io/scripting.html), [ttftotype42](https://github.com/kohler/lcdf-typetools) (maintained) and [ttftot42](https://github.com/nih-at/ttftot42) (dead), but the simple solution here only requires Ghostscript to run without any compilation. Being a Unicode CID-keyed font, rendered text strings are just 2 byte UTF-16 for which UTF-8 conversion is included.

The TrueType font is wrapped in PostScript syntax as `sfnts` binary data as for Type 42 base fonts. The CIDMap is compacted to reduce file size and command line options offer further compression.

Subsetting is supported. OpenType/TTF is supported but not OpenType/CFF as yet nor TrueType Collections. Vertical writing mode is not yet supported.

Basic Multilingual Plane only; surrogate pairs for supplementary characters show as `.notdef`.  
Depends on [pslutils](https://github.com/scriptituk/pslutils) files string.ps, file.ps, sort.ps & math.ps.  
Tested on GhostScript v8.7 to v9.22.

#### Usage:

* `gs -dQUIET -dNODISPLAY -dBATCH -dNOPAUSE <options> ttf2pscid2.ps`

or simply  

* `gs -q -o- -dNODISPLAY <options> ttf2pscid2.ps`

e.g.

* `gs -q -o- -dNODISPLAY -sttf=arial.ttf ttf2pscid2.ps`  
  converts arial.ttf to arial.t42, the .t42 extension indicates Type 42 derived PostScript code

then insert the generated code into a PostScript file and use it as normal, e.g.:
* `/ArialMT 10 selectfont (Hello) show` to scale and set the current font and Paint glyphs for 'Hello'.

Options are set using Ghostscript parameter switches (`-d` for definitions and `-s` for strings)
* `-sttf=file` and `-sttf=dir/` sets the TTF file(s) to convert  
  a trailing slash enumerates all files with .ttf or .otf extensions in the specified directory
* `-st42=file` and `-st42=dir/` sets the output filename or directory  
  the default output directory is the same as the TTF
* `-sinc=dir` sets the include path for [pslutils](https://github.com/scriptituk/pslutils) dependencies
* `-dpsname` sets the output basename to the PostScript font name contained in the TTF `name` table
* `-doptimise` remaps the glyph index numbering to produce a more compact CIDMap and hence smaller output file
* `-ssubset=chars` subsets the font to the given UTF-8 characters and sets `-doptimise`
* `-ducs2` interprets the subset characters as 16 bit UCS-2 (UTF-16BE BMP) instead of UTF-8
* `-dbinary` saves `sfnts` as binary data instead of ASCII hexadecimal
* `-dcompress` saves long `sfnts` strings as zlib/deflate compressed binary data instead of uncompressed and sets `-dbinary`
* `-dcomments` comments the `sfnts` strings for debugging
* `-dinfo` outputs tab-separated font information to the gs output file specified by -sOutputFile= or -o
* `-sargs=file` runs the given PostScript file of dictionary key/value pairs defining the required options, which take precedence

#### Examples:

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
* `gs -q -o- -dNODISPLAY -sargs=args.def ttf2pscid2.ps`  
  reads the options from file args.def (in valid PostScript syntax), e.g.:  
  `/subset (Fee: €25 (£22))`  
  `/psname true`  
  `/optimise true /compress true`  
  `/ttf (arial.ttf)`

#### Sample

`gs -q -o- -dNODISPLAY -sttf='Black Chancery.ttf' -ssubset='Hello World' -dcomments ttf2pscid2.ps`

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
	/CIDFontName /BlackChancery
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
	/FontBBox [-193 -336 1462 681] readonly
	/FontInfo <<
		/FamilyName (Black Chancery)
		/FullName (Black Chancery)
		/ItalicAngle 17560.0
		/Notice (Converted by ALLTYPE)
		/UnderlinePosition -80
		/UnderlineThickness 50
		/Weight (Regular)
		/extra <<
			/Tascent 668
			/isSymbolFont false
			/lineSpacing 1168
			/style (Regular)
			/unitsPerEm 1000
		>> readonly
		/isFixedPitch false
		/version (Converted from E:\WINDOWS\POWERPAK\BCHANCRY.FF1 by ALLTYPE)
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
<00010000000900800003001063767420C0476B650000009C000000106670676D
B1330283000000AC00000048676C79661B63F9AA000000F40000080068656164
6410423B000008F40000003668686561083400E20000092C00000024686D7478
19D60093000009500000002C6C6F636107F40A340000097C000000186D617870
059B03F7000009940000002070726570B801FF85000009B40000000400>
% cvt  table
<01B2028B03E8F31E0000B7BCBAABBE0000>
% fpgm table
<B0002C202FB00225338AB8100063B0022370B0024520B00425B00425496164B0
405058B00325233A1B215921B00123422058173C1B2159B00143102058173C1B
21592DB0012CC02D00>
% glyf table
%	gid=4 cid=72 H
<00010004FFD10381028B005E00801801B00040025E0141040031005500000000
2B40030B3E0240024C014104007D000A004B00002B40020F01400230014104FF
7E000E002F00002B311800B0004004071516034104FFE0004F000600002BB800
00B0003F40050E20555E04B0012AB80006B0013F40050715164F04B0012AB800
33B0043F400434444503B0012A30133436373232333706061511363637353426
2726262725060607060615141431171616331616331616172626272626271114
1617213636351106060706061514161716161721363633363635113426232206
0706061514161726262734343504A060010101E8253D4F853C112109160E013C
1F25090C0501020402040703192C090A10070C1D193923FECB2631163E203A61
2617081007FEC60204022138211A0D24132239505572610401A4727301011137
2AFEEE3F3E0A82222E14050B06010A1F1214250601017601010101041A28090D
05080601FEC52B340E1037250133051D1628784337330C0407030102103F2801
F0191807090F47415B7B2E01844F04070300000000>
%	gid=5 cid=87 W
<00010004FF8B0534028B007D005E1801B00040027D014104003A005F00000000
2B311800B000B000410400190065007700002BB00040060723244344054104FF
E00059000600002BB8001AB0003F4006004C535F7D05B0012AB80006B0013F40
0707232443445906B0012A301334363732323325160607060615141617133636
3734363736363126262726262726262725060607060615141617133636373436
3736363736363734343534262726262725060607060607030303262627262631
2626272626232206070606151416171616333232333636273426271616151406
07060623222627262635049F60030503010303231514220101BD081D10010116
2311170B0C1C1507110901330D170A18190301B7072213010114240401010142
27091108016F304918101405D2C2AC02432728440407041533290A140A2E4E04
040D473C020402253E0105050C0C02010946380F2E1928420189837B03010105
0606140F010502FE2012512D0203023E672B3E16192310050A06010508030A14
0F040805FE311565380203013F6F12060905030502313910030603010A2C1910
1F0CFD8A01F8FE0807AB6668AE091409355903030C5C560F2211375C043A2409
1209091A0F050A0521360B0C15554A0000>
%	gid=6 cid=100 d
<0002001AFFFA0207029B0037004800681801400717292A384748064003013702
4104FFA40016000000002BB000B00041040060003E002600002B311800400248
01B0004104FFE70038002900002BB80029B0003F4003384802B0012AB8002DB0
013F40022E01B0012AB80010B0043F4003042002B0012A300111161637323637
0606070606070606072226272626353506060706060706062322222726263534
3633353636333706060706060706061507060607061415141637323637363637
35017F0107050A20140307050C2312060B0504120A0D1608241707100915290F
0204021E319A6D014A42590B170C101F0D0D115E4B560501231A0C1B0E0E1B0C
024BFDEF080601110B060B06101C0603020106070A221837092515070E071217
01044C428C924543670114180609040101050BBA01683E040804342B01080707
140BE00000>
%	gid=7 cid=101 e
<0002001EFFFC018701AF00220038003D1801180040023801B0004104FFC10023
000300002BB000B000410400440010001C00002BB80003B0003F400100B0012A
B8001CB0043F400100B0012A3037343633321617161617071616171616333236
3736363736363106062322262726263537060615141617363637363637363635
3426272626231E875B121E0E102216F30C20100F1D0C060B0611200E13172761
3E44460D0705A925260707082112080F060F140203051A1AB076890608081C16
F215150504020101040D0809113C3D432913250FC1015635172E14081E130711
0812220D061209101900000000>
%	gid=8 cid=108 l
<00010014FFFA00FC029B001E0033180140020F014003011E0241040060000E00
0000002B311800B80004B0013F40020501B0012AB8001BB0043F400100B0012A
3037113436333706060706060706061511141633363637060607060623222635
144D415A0A170C101F0C0E1007070C29190E2815101D0D20194E01A045670113
18060A040101050BFE040A09011A161A2F100C0D2E25000000>
%	gid=9 cid=111 o
<0002001AFFFA01A301B0000C001F00611801B00040020C014104005700100000
00002BB000B0004104FFA40019000600002B31180040021F01B0004104FFD100
0D000300002BB000B0004104002F0016000900002BB80003B0003F40030D1F02
B0012AB80009B0043F40021601B0012A30373436333216151406232226353706
06151416171616333236353426272626231A845656598953525BB82C34020609
31342B360304093433CB598C7A535C8D7957B801473510381E2C4A4937122816
3355000000>
%	gid=10 cid=114 r
<0001FFF2FFFA017B01B0004400451801400336370240023E014104005C000F00
3D00002B311800B000400215014104FFAB0041000900002BB80009B0003F4002
1501B0012AB8003DB0043F40023701B0012A3003363637363637363633321607
1414153636373636333216151406070606070606073636373636373436353426
2322060706060706063115140607060607113426232206070E060E0A080F0910
1F09210B0109130A1D412A1D1D1A1308130A081007010201030602010E130818
0C0509051017120E0D1F110A0A09180E013E08170C0911081015483F070F070C
1C0E2945291A122B160A140908100702050309190D050905141D120D050B0513
21BA01110A090E02014B070D0D0F000000>
% head table
<00010000000000004BBE284B5F0F3CF5000903E8000000000000000000000000
00000000FF3FFEB005B602A900000005000100000000000000>
% hhea table
<0001000002AAFEB00096046EFF3FFDB805B20001000000000000000000000000
0000000B00>
% hmtx table
%	hMetrics
<03170011031700110317001100FA000003900004046E000401CA001A019E001E
00DC001401C2001A0192FFF200>
%	leftSideBearing
% loca table
<0000000000000000000000CA01B2025402CA03160378040000>
% maxp table
<00010000000B0160000601600006000100000002000200010580013200010001
00>
% prep table
<B801FF8500>
] readonly put

cidfont dup /CIDFontName get exch /CIDFont defineresource
/CIDFontName get /Identity-H [2 index] composefont pop

```

then emit text like…

```postscript
/BlackChancery 10 selectfont
0 0 moveto
(Hello World) show
showpage
```

Easy!
