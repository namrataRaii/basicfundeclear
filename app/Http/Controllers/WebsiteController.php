<?php

namespace App\Http\Controllers;

use App\Models\AboutContent;
use App\Models\CreateSection;
use App\Models\IndexContent;
use App\Models\ShortsModel;
use App\Models\ShowAssignModel;
use App\Models\ShowModel;
use App\Models\ShowTypeModel;
use App\Models\TeamMember;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

class WebsiteController extends Controller
{
    public function index()
    {
        $currentRoute = Route::currentRouteName();
        if($currentRoute=='show'){
            $data['title'] = 'Our Shows';
        }
        elseif($currentRoute=='shorts'){
            $data['title'] = 'Our Shorts';
        }
        elseif($currentRoute=='contact'){
            $data['title'] = 'Contact Us';
        }
        else{
            $data['title'] = 'Index Page';
        }
        $data['shows'] = ShowModel::where('status', 1)->orderBy('position_by', 'asc')->get();
        $data['indexcontent'] = IndexContent::where('status', 1)->orderBy('position_by', 'asc')->first();
        $data['metadata'] = IndexContent::where('status', 1)->orderBy('position_by', 'asc')->first();
        $data['showtypes'] = ShowTypeModel::where('status', 1)
        ->orderBy('position_by', 'asc')
        ->withCount('assignedShows') // Include count of assigned shows for each show type
        ->get();

        // Get the relevant showtype_ids from the showassign table
        $assignedShowTypeIds = ShowAssignModel::where('status', 1)->pluck('showtype_id');

        // Filter assignshowtypes based on the retrieved showtype_ids
        $data['assignshowtypes'] = ShowTypeModel::whereIn('id', $assignedShowTypeIds)
            ->where('status', 1)
            ->orderBy('position_by', 'asc')
            ->get();
        $data['showassigns'] = ShowAssignModel::where('status', 1)->with('showtype')->orderBy('position_by', 'asc')->get();
        $data['firstsectionsheadings'] = CreateSection::where(['status'=>1,'id'=>1])->orderBy('position_by', 'asc')->first();
        $data['secondsectionsheadings'] = CreateSection::where(['status'=>1,'id'=>2])->orderBy('position_by', 'asc')->first();
        $data['shortssectionsheadings'] = CreateSection::where(['status'=>1,'id'=>3])->orderBy('position_by', 'asc')->first();
        
        $data['shorts'] = ShortsModel::where('status', 1)->orderBy('position_by', 'asc')->get();

        return view('website.index', $data);
    }


    public function about()
    {
        $data['title'] = 'About Page';
        $data['aboutcontent'] = AboutContent::where('status', 1)->orderBy('position_by', 'asc')->first();
        $data['metadata'] = AboutContent::where('status', 1)->orderBy('position_by', 'asc')->first();
        
        $data['teammembers'] = TeamMember::where('status',1)->orderBy('position_by', 'asc')->get();
        return view('website.about',$data);
    }

    public function privacypolicy()
    {
        $data['title'] = 'Privacy Policy';
        return view('website.privacypolicy',$data);
    }


    public function ShowDetails(Request $request, $title, $subtitle, $id)
    {
        // dd($request->input('id'));
        $data['title'] = 'Show Details';
       
    // dd($request->all());
        if (!empty($id)) {
            $originalId = base64_decode($id);

            $allshowtypes = ShowTypeModel::where(['id' => $originalId, 'status' => 1])->orderBy('position_by', 'asc')->first();
    
            if (!empty($allshowtypes)) {
                $allshowdetails = ShowAssignModel::where(['showtype_id' => $originalId, 'status' => 1])->orderBy('position_by', 'asc')->get();
                $data['showtypes'] = $allshowtypes;
                $data['showdetails'] = $allshowdetails;
    
                if (empty($allshowtypes)) {
                    $data['showdetails'] = '';
                }
    
                return view('website.showdetails', $data);
            } else {
                return redirect()->back();
            }
        } else {
            return redirect()->back();
        }
    }
    
}
