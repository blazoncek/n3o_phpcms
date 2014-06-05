function isascii(c) { return (c>=' '&&c<='~') } 
function isalpha(c) { return ((c>='0'&&c<='9')||(c>='a'&&c<='z')||(c>='A'&&c<='Z')||c=='-'||c=='_'||c=='~') } 
function ischar(c)  { return ((c>='a'&&c<='z')||(c>='A'&&c<='Z')||c=='-'||c=='_'||c=='~') } 
function isdigit(c) { return ((c>='0'&&c<='9')||c==','||c=='.'); }

function atoi(s) {
	var t=0;
	for (var i=0;i<s.length;i++) {
		var c=s.charAt(i);
		if(!isdigit(c))
			return t;
		else t=t*10+(c-'0');
	}
	return t;
}

function empty(obj) {
	return (obj.value.length == 0);
}

function strpos(haystack, needle, offset) {
	var i = (haystack+'').indexOf(needle, (offset ? offset : 0));
	return i === -1 ? false : i;
}

function setCookie( sName, sValue, nDays ) {
	var expires = "";
	if ( nDays ) {
		var d = new Date();
		d.setTime( d.getTime() + nDays * 24 * 60 * 60 * 1000 );
		expires = "; expires=" + d.toGMTString();
	}
	document.cookie = sName + "=" + sValue + expires + "; path=/";
}

function getCookie( sName ) {
	var re = new RegExp( "(\;|^)[^;]*(" + sName + ")\=([^;]*)(;|$)" );
	var res = re.exec( document.cookie );
	return res != null ? res[3] : null;
}

function removeCookie( name ) {
	setCookie( name, "", -1 );
}

function emailOK(obj) {
	email = obj.value;
	invalidChars = "/:,;[]{}()*?\\";

	if (email == "") return false;
	for (i=0; i<invalidChars.length; i++) { // does it contain any invalid characters?
		badChar = invalidChars.charAt(i);
		if (email.indexOf(badChar,0) > -1)
			return false;
	}
	atPos = email.indexOf("@",1);
	if (atPos == -1) return false;	// there must be one "@" symbol
	if (email.indexOf("@",atPos+1) != -1) return false;	// and only one "@" symbol
	periodPos = email.indexOf(".",atPos)
	if (periodPos == -1) return false;	// and at least one "." after the "@"
	if (periodPos+3 > email.length)	return false;	// must be at least 2 characters after the "."
	return true;
}

function pwdOk(pwd1,pwd2) {
    if (pwd1.value == pwd2.value)
		return true;
	return false;
} 

function IsNumeric(str) {
	if (str.value.length <= 0) return false;
	for (var i=0; i<str.value.length; i++) {
		if (!isdigit(str.value.charAt(i)))
			return false;
	}
	return true;
}

function GetFullHostName() {
	var tempL = new String(document.location);
	var start;
	var end;
	var newLocation = new String();

	start = tempL.indexOf("//") + 2;

	newLocation = tempL.substring(start,tempL.length);
	newLocation = newLocation.substring(0,newLocation.indexOf("/"));

	return newLocation;
}

function inputNum(field,e,val) {
	var key;	

	if (navigator.appName == 'Netscape') {
		key = e.which;
		evnt = e;

		if ( (key >= 48 && key <= 57) || key < 32 )
			return true;

		if (val!=null)
			for ( i=0;i < val.length; i++ )	{
				if ( val[i] == key )
					return true;
			}
		return false;
	} else {
		key = window.event.keyCode;
		window.event.returnValue = true;
		
		if ( key >= 48 && key <= 57 )
			return;
		
		if (val!=null)
			for ( i=0; i < val.length; i++ ) {
				if ( val[i] == key )
					return;
			}
		window.event.returnValue = false;
	}
}

function vnosStevilke(field,e) {
	return inputNum(field,e,new Array(44,45));
}

/**
 * Function : dump()
 * Arguments: The data - array,hash(associative array),object
 *    The level - OPTIONAL
 * Returns  : The textual representation of the array.
 * This function was inspired by the print_r function of PHP.
 * This will accept some data as the argument and return a
 * text that will be a more readable version of the
 * array/hash/object that is given.
 * Docs: http://www.openjs.com/scripts/others/dump_function_php_print_r.php
 */
function dump(arr,level) {
	var dumped_text = "";
	if(!level) level = 0;
	
	//The padding given at the beginning of the line.
	var level_padding = "";
	for(var j=0;j<level+1;j++) level_padding += "    ";
	
	if(typeof(arr) == 'object') { //Array/Hashes/Objects 
		for(var item in arr) {
			var value = arr[item];
			
			if(typeof(value) == 'object') { //If it is an array,
				dumped_text += level_padding + "'" + item + "' ...\n";
				dumped_text += dump(value,level+1);
			} else {
				dumped_text += level_padding + "'" + item + "' => \"" + value + "\"\n";
			}
		}
	} else { //Stings/Chars/Numbers etc.
		dumped_text = "===>"+arr+"<===("+typeof(arr)+")";
	}
	return dumped_text;
}

/**
 * Versao JavaScript da funcao var_dump do PHP
 * @param mixed ... Qualquer valor
 * @return string Informacoes do valor
 */
function var_dump(/* ... */) {
    
    /**
     * Recursao do metodo var_dump
     * @param midex item Qualquer valor
     * @param int nivel Nivel de indentacao
     * @return string Informacoes do valor
     */
    this.var_dump_rec = function(item, nivel) {
        if (var_dump.max_iteracoes > 0 && var_dump.max_iteracoes < nivel) {
            return this.indentar(nivel) + "*MAX_ITERACOES(" + var_dump.max_iteracoes+ ")*\n";
        }
        if (item === null) {
            return this.indentar(nivel) + "NULL\n";
        } else if (item === undefined) {
            return this.indentar(nivel) + "undefined\n";
        }

        var str = '';
        var tipo = typeof(item);
        switch (tipo) {
        case 'object':
            var classe = this.get_classe(item);
            switch (classe) {
            case 'Array':
                str += this.indentar(nivel) + "Array(" + item.length + ") {\n";
                for (var i in item) {
                    str += this.indentar(nivel + 1) + "[" + i + "] =>\n";
                    str += this.var_dump_rec(item[i], nivel + 1);
                }
                str += this.indentar(nivel) + "}\n";
                break;

            case 'Number':
            case 'Boolean':
                str += this.indentar(nivel) + classe + "(" + item.toString() + ")\n";
                break;

            case 'String':
                str += this.indentar(nivel) + classe + "(" + item.toString().length + ") \"" + item.toString() + "\"\n";
                break;
            
            default:
                str += this.indentar(nivel) + "object(" + classe + ") {\n";
                var exibiu = false;
                for (var i in item) {
                    exibiu = true;
                    str += this.indentar(nivel + 1) + "[" + i + "] =>\n";
                    try {
                        str += this.var_dump_rec(item[i], nivel + 1);
                    } catch (e) {
                        str += this.indentar(nivel + 1) + "(Erro: " + e.message + ")\n";
                    }
                }
                if (!exibiu) {
                    str += this.indentar(nivel + 1) + "JSON(" + JSON.stringify(item) + ")\n";
                }
                str += this.indentar(nivel) + "}\n";
                break;
            }
            break;
        case 'number':
            str += this.indentar(nivel) + "number(" + item.toString() + ")\n";
            break;
        case 'string':
            str += this.indentar(nivel) + "string(" + item.length + ") \"" + item + "\"\n";
            break;
        case 'boolean':
            str += this.indentar(nivel) + "boolean(" + (item ? "true" : "false") + ")\n";
            break;
        case 'function':
            str += this.indentar(nivel) + "function {\n";
            str += this.indentar(nivel + 1) + "[code] =>\n";
            str += this.var_dump_rec(item.toString(), nivel + 1);
            str += this.indentar(nivel + 1) + "[prototype] =>\n";
            str += this.indentar(nivel + 1) + "object(prototype) {\n";
            for (var i in item.prototype) {
                str += this.indentar(nivel + 2) + "[" + i + "] =>\n";
                str += this.var_dump_rec(item.prototype[i], nivel + 2);
            }
            str += this.indentar(nivel + 1) + "}\n";

            str += this.indentar(nivel) + "}\n";
            break;
        default:
            str += this.indentar(nivel) + tipo + "(" + item + ")\n";
            break;
        }
        return str;
    };

    /**
     * Devolve o nome da classe de um objeto
     * @param Object obj Objeto a ser verificado
     * @return string Nome da classe
     */
    this.get_classe = function(obj) {
        if (obj.constructor) {
            return obj.constructor.toString().replace(/^.*function\s+([^\s]*|[^\(]*)\([^\x00]+$/, "$1");
        }
        return "Object";
    };

    /**
     * Retorna espacos para indentacao
     * @param int nivel Nivel de indentacao
     * @return string Espacos de indentacao
     */
    this.indentar = function(nivel) {
        var str = '';
        while (nivel > 0) {
            str += '  ';
            nivel--;
        }
        return str;
    };

    var str = "";
    var argv = var_dump.arguments;
    var argc = argv.length;
    for (var i = 0; i < argc; i++) {
        str += this.var_dump_rec(argv[i], 0);
    }
    return str;
}
var_dump.prototype.max_iteracoes = 0;