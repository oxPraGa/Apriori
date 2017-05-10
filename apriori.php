<?php
class Apriori{
  private $minSupport;
  private $minCof;
  private $mat = array();
  private $nbItem;
  private $nbTransaction;
  private $rules = array();
  private $allFrequents = array();
  private $tmpCombin = array();
  public function __construct($nbItem,$nbTransaction){
    $this->nbItem = $nbItem;
    $this->nbTransaction = $nbTransaction;
  }
  //
  // set the min support
  //
  public function setMinSuppor($minSupport){
    $this->minSupport = $minSupport;
  }

  //
  // set the min confindence
  //
  public function setMinCof($minCof){
    $this->minCof = $minCof/100;
  }

  //
  // get all frequents itemset
  //
  public function getAllFrequents(){
    return $this->allFrequents;
  }

  //
  // get all rules itemset
  //
  public function getAllRules(){
    return $this->rules;
  }

  //
  //  Create random matrix
  //
  public function creatRandVal(){
    for($i=0;$i<$this->nbItem;$i++){
      $tmp[0] = $i;
      for($j=1;$j<$this->nbTransaction+1;$j++){
        $tmp[$j] = rand(0,1);
        //$tmp[$j] =  $j % 2;
      }
      $this->mat[$i] = $tmp;
    }
  }
  public function showMat(){
    for($i=0;$i<$this->nbItem;$i++){
      echo chr(65+$i)."\n";
      print_r($this->mat[$i]);
    }
  }
  //
  //  function for do all etapes
  //
  public function process(){
    // $time_old = time();
    // echo "Start time : ".$time_old."\n";
    $this->calculAllFrequents();
    $this->genrateRules();
    // $time = time() - $time_old;
    // echo "nbr frequent => ". count($this->allFrequents);
    // echo "end ".time()."\n";
    // echo "Time : ".$time." s \n";
    // print_r($this->rules);
  }
  public function showTmp($tmp){
    for($i=0;$i<count($tmp);$i++){
    //  echo chr(65+$tmp[$i][0])."\n";
    //  print_r(array_slice($tmp[$i] , 1));
      print_r($tmp[$i] );
      echo "---\n";
      echo $this->calculSupport($tmp[$i]);
      echo "---\n";
      echo "\n";
    }
  }

  //
  // get all frequents from candidat
  //
  public function calculAllFrequents(){
    $freq = $this->getFrequent($this->mat);
    $this->allFrequents = array_merge ($this->allFrequents , $freq);
    $i = 0;
    while(count($freq) != 0 && $i < $this->nbItem ){
      $cand = $this->genrateCandidat($freq);
      $freq = $this->getFrequent($cand);
      if(count($freq) != 0 )
        $this->allFrequents = array_merge($this->allFrequents , $freq);
      $i++;
    }
  }
  //
  //  genrate rules
  //
  public function genrateRules(){
    $nbrules = 0;
    foreach ($this->allFrequents as  $value) {
      $itemset = explode("," , $value[0] );
      if(count($itemset) > 1){
        foreach ($this->combinAll($itemset, count($itemset)-1 ) as $antic) {
          $dec = implode($this->myArrayDiff($itemset , $antic) , ",");
          $antic = implode($antic , ",");
          $confid = $this->calculConfidence($antic , $dec);
          if( $confid >= $this->minCof ){
            $this->rules[] =  array($antic ,$dec , $confid );
            $nbrules++;
          }
        }
      }
    }
  }
  //
  // get all frequents itemset from mat
  //
  public function getFrequent($mat){
    $tmp = array();
    for($i=0;$i<count($mat);$i++){
      $tmp1 = array_slice($mat[$i] ,1 );
      if(array_sum($tmp1) / $this->nbTransaction  >= $this->minSupport ){
        array_push($tmp , $mat[$i]);
      }
    }
    return $tmp;
  }
  //
  // function to genrate Candidat
  //
  public function genrateCandidat($array){
    $candidat = array();
    $nmb =0;
    for($i=0;$i<count($array);$i++){
      // get all item
      $item = explode( "," , $array[$i][0] );
      for($j=$i+1;$j<count($array);$j++){
        // get all item for next column
        $tmpItem1 = explode(",",$array[$j][0]);
        $tmpItem = $tmpItem1[count($tmpItem1)-1];
        $tmpItemps2 = array_slice($tmpItem1 ,0,count($tmpItem1)-1 );
        $tmp = array();
        $tmp = $item;
        $tmpItemps1 = array_slice($item ,0,count($item)-1 );
        if( count(array_diff($tmpItemps1 , $tmpItemps2)) == 0  || count($item) == 1){
          array_push($tmp , $tmpItem );
          $tmpwiw = array();
          $tmpwiw[0] = implode($tmp, ",");
          $candidat[$nmb]  = array_merge($tmpwiw , $this->arrayPorduct(array_slice($array[$i],1) ,  array_slice($array[$j],1))  );
          $nmb++;
        }else{
          // stop combination because all transaction != to the $i transaction
          //  $i = $j;
          break;
        }
      }
    }
    return $candidat;
  }
  //
  // function for calcul condifidence
  //
  public function calculConfidence($antic , $dec ){
    // calcul support of all item in antic & dec
    $supporleft = 0;
    $supporright = 0;
    // get item set from anicedebt
    $tmpant = explode(",",$antic[0]);
    $tmpdec = explode(",",$dec[0]);
    $tmp = array();
    foreach ($tmpant as $value) {
      $val = $this->mat[$value];
      $data = array_slice($val , 1);
      // if empty data tmp =  data
      if( empty($tmp) )
        $tmp = $data;
      else
        $tmp = $this->arrayPorduct($tmp , $data);
    }
    $supporleft = array_sum($tmp);
    foreach ($tmpdec as  $value) {
      $val = $this->mat[$value];
      $data = array_slice($val , 1);
      // if empty data tmp =  data
        $tmp = $this->arrayPorduct($tmp , $data);
    }

    $supporright =  array_sum($tmp);
    return $supporright/$supporleft;
  }
  //
  // function for array product
  //
  public function arrayPorduct($array1 , $array2 ){
    $product = array();
    foreach ($array1 as $key => $value) {
      $product[$key] = $value * $array2[$key];
    }
    return $product;
  }
  //
  // function for support calcul
  //
  public function calculSupport($array){
    // slice the first case
    return array_sum(array_slice($array , 1 ));
  }

  //
  // function for combin array element
  // via : http://stackoverflow.com/questions/4279722/php-recursion-to-get-all-possibilities-of-strings/8880362#8880362
  public function getCombinations($base,$n){
        $baselen = count($base);
        if($baselen == 0){
            return;
        }
        if($n == 1){
          $return = array();
          foreach($base as $b){
            $return[] = array($b);
          }
          return $return;
        }else{
          $oneLevelLower = $this->getCombinations($base,$n-1);
          $newCombs = array();
            foreach($oneLevelLower as $oll){
              $lastEl = $oll[$n-2];
              $found = false;
              foreach($base as  $key => $b){
                if($b == $lastEl){
                  $found = true;
                continue;
                //last element found
                }
                if($found == true){
                //add to combinations with last element
                  if($key < $baselen){
                    $tmp = $oll;
                    $newCombination = array_slice($tmp,0);
                    $newCombination[]=$b;
                    $newCombs[] = array_slice($newCombination,0);
                  }
                }
              }
            }
          }
            return $newCombs;
        }
    //
    //  list all subsets of an array
    //
    public function combinAll($array, $to){
      $tmp = array();
      for($i=1;$i<=$to;$i++){
        foreach ($this->getCombinations($array,$i) as $value) {
          $tmp[] = $value;
        }
      }
      return $tmp;
    }
    //
    // array diff with some modification
    //
    public function myArrayDiff($array1,$array2){
      if( $array1 > $array2 ) return array_diff($array1,$array2);
      return array_diff($array2 , $array1);
    }
}




//print_r(array_diff(array(1) , array(1,2,3,4,5) ));
 //

?>
