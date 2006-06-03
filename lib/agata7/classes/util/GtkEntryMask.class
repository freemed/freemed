<?php

/***********************************************************/
/* GtkEntryMask
/* by Pablo Dall'Oglio December, 2004, 21
/* pablo@dalloglio.net
/***********************************************************/
class GtkEntryMask extends GtkEntry
{
    /***********************************************************/
    /* Constructor Method
    /***********************************************************/
    function GtkEntryMask($mask)
    {
        GtkEntry::GtkEntry();
        GtkEntry::set_max_length(strlen(trim($mask)));
        # en: allowed mask splitters $fuck = 'lsdkfj "
        # pt: divisores permitidos na máscara
        $this->chars = array('-', '_', '.', '/', '\\', ':', '|', '(', ')', '[', ']', '{', '}');
        $this->mask  = $mask;

        GtkEntry::set_text('');

        # en: Any change, call method Filter
        # pt: Método chamado quando há alguma alteração no campo
        GtkEntry::connect_object_after('changed', array(&$this, "Filter"));
    }
    
    /***********************************************************/
    /* en: Set the GtkEntry content, unset_flags to avoid
    /*     recursion

    /* pt: Troca o conteúdo do GtkEntry, desliga as flags
    /*     para evitar recursao infinita...
    /***********************************************************/
    function Set($text)
    {
        # en: turn the signal off to avoid infinite recursion.
        # pt: desliga o sinal para evitar recursão infinita.
        GtkEntry::unset_flags(GTK_CONNECTED);
        GtkEntry::set_text($text);
        GtkEntry::set_flags(GTK_CONNECTED);
    }
    
    /***********************************************************/
    /* en: Called after each change
    /*     mask the content and validate it

    /* pt: Chamada depois de qualquer mudança para
    /*     mascarar o conteúdo e validá-lo
    /***********************************************************/
    function Filter()
    {
        $text = GtkEntry::get_text();
        # en: removes the splitters
        # pt: remove os separadores
        $text = $this->unmask($text);
        $len  = strlen(trim($text));
        # en: mask again
        # pt: mascara novamente
        $new  = $this->mask($this->mask, $text);
        # en: set the new value in 1 milisecond.
        # pt: grava o novo valor daqui há 1 milisegundo.
        Gtk::timeout_add(1, array($this, 'Set'), $new);
        Gtk::timeout_add(1, array($this, 'Validate'));
    }

    /***********************************************************/
    /* en: Validate the typed character

    /* pt: Valida o caracter digitado
    /***********************************************************/
    function Validate()
    {
        # en: get the content.
        # pt: obtem o conteudo
        $text = GtkEntry::get_text();
        $mask = $this->mask;
        $len  = strlen($text);
        # en: get the typed char
        # pt: obtem o caracter digitado
        $text_char = substr($text, $len-1, 1);

        # en: get the mask char
        # pt: obtem o caracter da máscara
        $mask_char = substr($mask, $len-1, 1);

        # en: matches the typed char with the mask char
        # pt: compara o caracter digitado com o da máscara
        if ($mask_char == '9')
            $valid = ereg("([0-9])", $text_char);
        elseif ($mask_char == 'a')
            $valid = ereg("([a-z])", $text_char);
        elseif ($mask_char == 'A')
            $valid = ereg("([A-Z])", $text_char);
        elseif ($mask_char == 'X')
            $valid = (ereg("([a-z])", $text_char) or
                    ereg("([A-Z])", $text_char) or
                    ereg("([0-9])", $text_char));

        # en: if not valid, removes this last typed char
        # pt: se não válido, remove este último caracter
        if (!$valid)
        {
            $this->Set(substr($text, 0, -1));
        }
    }

    /***********************************************************/
    /* en: Put the content in Mask format

    /* pt: Coloca o conteúdo digitado no formato da máscara
    /***********************************************************/
    function mask($mask, $text)
    {
        $z = 0;
        # en: run over the mask chars
        # pt: percorre os caracteres da máscara
        for ($n=0; $n < strlen($mask); $n++)
        {
            $mask_char = substr($mask, $n, 1);
            $text_char = substr($text, $z, 1);
    
            # en: check when needs to concatenate a splitter
            # pt: verifica quando concatenar o divisor
            if (in_array($mask_char, $this->chars))
            {
                if ($z<strlen($text))
                    $result .= $mask_char;
            }
            else
            {
                $result .= $text_char;
                $z ++;
            }
            
        }
        return $result;
    }


    /***********************************************************/
    /* en: Removes the mask
    
    /* pt: Remove a máscara
    /***********************************************************/
    function unmask($text)
    {
        # en: run over the typed text
        # pt: percorre o texto digitado
        for ($n=0; $n <= strlen($text); $n++)
        {
            $char = substr($text, $n, 1);
            # en: returns if isn't a splitter
            # pt: retonra se não é um divisor
            if (!in_array($char, $this->chars))
            {
                $result .= $char;
            }
        }
        return $result;
    }
}
?>