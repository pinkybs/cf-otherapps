/*
  Programmer  : Song Hwi Park
  Email       : Linkage8055@hotmail.com
  Last Update : 2009-02-27
*/

package PSHLibs.Common {
  public class PSHGraphics { 
    public function PSHGraphics() { 
    } 
	
    public function ColorToRGB(Value:int) {
      var Result:Object=new Object();
      Result.R = Value >> 16; 
      var Aux = Value - (Result.R << 16);
      Result.G = Aux >> 8; 
      Result.B = Aux - (Result.G << 8); 
      Result.RP=Result.R / 255;
      Result.GP=Result.G / 255;
      Result.BP=Result.B / 255;
      return Result;
    }

    public function IntToHex(Value:int = 0):String {
      var Mask:String = "000000";           
      var Str:String = Mask + Value.toString(16).toUpperCase();            
      return "0x" + Str.substr(Str.length - 6);        
    } 

  }
}