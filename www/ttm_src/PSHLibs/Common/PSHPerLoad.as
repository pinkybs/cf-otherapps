/*
  Programmer  : Song Hwi Park
  Email       : Linkage8055@hotmail.com
  Last Update : 2009-02-27
*/

package PSHLibs.Common {
  import flash.display.MovieClip;
  import flash.display.Stage;
  import flash.events.Event;
  import flash.events.ProgressEvent;

  public class PSHPerLoad extends MovieClip {
    public static var MStage:Stage;
    public var OnProgress:Function=null;
    public var OnComplete:Function=null;

    public function PSHPerLoad() {}

    public function Init() { 
      MStage=this.stage;
      addEventListener(Event.ENTER_FRAME, HandleProgress);
    }

    private function HandleProgress(E: Event): void { 
      var Loaded:Number = MStage.loaderInfo.bytesLoaded;
      var Total:Number  = MStage.loaderInfo.bytesTotal;
      var Percent:Number = Loaded/Total;
      if(OnProgress!=null) OnProgress(Math.floor(Percent*100), Loaded, Total);    
      if(Loaded>=Total) { 
        removeEventListener(Event.ENTER_FRAME, HandleProgress); 
        if(OnComplete!=null) OnComplete();
      }
    } 
  }
}