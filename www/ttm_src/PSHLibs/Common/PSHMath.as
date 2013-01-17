/*
  Programmer  : Song Hwi Park
  Email       : Linkage8055@hotmail.com
  Last Update : 2009-02-27
*/

package PSHLibs.Common {
  public class PSHMath { 
    public function PSHMath() { 
    } 
	
    public function DegToRad(Deg: Number): Number {
      return 1.745329E-002*Deg;
    } 
	
    public function Distance2D(X1, Y1, X2, Y2) {
      var DX = X2-X1;
      var DY = Y2-Y1;
      return Math.sqrt(DX*DX+DY*DY);
    }

    public function RotateRadius2D(X, Y, XRadius, YRadius, Angle):Object {
  	  var R=new Object();
      var Rad=DegToRad(Angle);
      R.DX=XRadius*Math.cos(Rad);
      R.DY=YRadius*Math.sin(Rad)*-1;
      R.X=X+R.DX;
      R.Y=Y+R.DY;
      return R;
    }
    
    public function RotateCenter2D(CX, CY, X, Y, Angle):Object {
      var R=new Object();
      var Rad=DegToRad(Angle);
      X=(X-CX);
      Y=(Y-CY);
      R.DX=(X*Math.sin(Rad)-Y*Math.cos(Rad));
      R.DY=(Y*Math.sin(Rad)+X*Math.cos(Rad));
      R.X=R.DX+CX;
      R.Y=R.DY+CY;
      return R; 
    }
    
    public function Drop2D(X, Y, Angle, Gravity, Power, Time):Object {
  	  var R=new Object();
      var Rad=DegToRad(Angle);
      Power=(Power/3)+10;
      R.G=Gravity2D(Gravity, Time);
      R.X=X+(Power*Math.cos(Rad));
      R.Y=Y-(Power*Math.sin(Rad)-R.G);
      R.XLG=X+(Power*Math.cos(Rad)-R.G);
      R.XRG=X-(Power*Math.cos(Rad)-R.G);
      R.A=R.G;
      return R; 
    }

    public function Gravity2D(Gravity, Time):Number {
      return (1/2*Gravity*Math.sqrt(Time));
    }
	
	public function abs(number):Number {
	  return Math.abs(number);
	}
  }
}