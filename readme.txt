Idea behind compressor is to use available minimisers/obfuscators and localhost server to obfuscate and minimise whole folder structure without installing any third party applications/programs.

Work currently only IE as other browsers' security is more restrict.

When folder has been written to text box and compress button clicked, the compressor creates a folder with same name with "_min" at the end and then it start going through all files within given folder.


Each time it meets files with css, js or html filetype, compressor will minimise, obfuscate and copy it to "_min" folder. In case it is not possible to minimise/obfuscate, it will just copy the file to "_min" folder.

To get this working:
- drop "compress" to your localhost
- open IE at localhost/compress/
- write directory you need to minimise/obfuscate
- click select
- wait