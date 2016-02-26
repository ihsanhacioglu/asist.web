

<?php
function hesapla($a,$b)
{
    $topla=$a+$b;
    return $topla;
}

class hesapla 
{
  var $sayi1 = 2; 
  var $sayi2 = 2; //sayilar bos ise sayilar 2 olsun
  
  function hesapla($sayi1, $sayi2) 
  {
      $this->sayi1=$sayi1; 
      $this->sayi2=$sayi2;
  } //sayi getir
  function topla() 
  { 
      return $this->sayi1+$this->sayi2 ; 
  }    //sayilari topla
  function eksi()  
  { 
      return $this->sayi1-$this->sayi2 ; 
  }    //sayilari eksilt
  function carp()  
  { 
      return $this->sayi1*$this->sayi2 ; 
  }    //sayilari carp
  function boel()  
  { 
      return $this->sayi1/$this->sayi2 ; 
  }    //sayilari böl
}


?>