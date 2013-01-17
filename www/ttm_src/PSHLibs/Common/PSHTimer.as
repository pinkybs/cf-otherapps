/*
  Programmer  : Song Hwi Park
  Email       : Linkage8055@hotmail.com
  Last Update : 2009-02-27
*/

package PSHLibs.Common {
  import flash.utils.Timer;

  public class PSHTimer extends Timer { 
    public var MC:Object;
    public function PSHTimer(delay:Number, repeatCount:int = 0) { 
       super(delay, repeatCount);
    }
  }
}