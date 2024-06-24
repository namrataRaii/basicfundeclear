<?php

namespace App\Http\Controllers;

use App\Models\IndexContent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
use Yajra\DataTables\Facades\DataTables;

class IndexContentController extends Controller
{
    public function index(Request $request)
    {
        $tableName = (new IndexContent)->getTable();
        $data['tablename'] = $tableName;
  
     
        $data['title'] = 'Index Content';
        $data['editcontent'] = IndexContent::where('status', 1)->first();
   
     
        return view('admin.indexcontent', $data);
    }


    public function save(Request $request)
    {
        $total = IndexContent::count();
        $position_by = $total + 1;
    
        // Initialize $filePath
        $filePath = '';
    
        // Handle file upload
        if ($request->hasFile('content')) {
            // Generate a unique name for the file
            $fileName = time() . '.' . $request->content->extension();
    
            // Upload the file to the 'website' folder in the S3 bucket
            $file = $request->file('content');
            $filePath = $file->store('website', 's3');
    
            // Delete the old content if updating an existing record
            if (!empty($request->id)) {
                $indexcontent = IndexContent::find($request->id);
                if (!empty($indexcontent->content)) {
                    // Delete the old file from S3
                    Storage::disk('s3')->delete($indexcontent->content);
                }
            }
        }
    
        // Check if it's an update operation
        if (!empty($request->id)) {
            // Validate the incoming request data
            $request->validate([
                'heading' => 'required',
                'subheading' => 'required',
                'watchlink' => 'required|url',
                'attribute' => 'required|string',
                'description' => 'required|string',
                'metatitle' => 'required|string',
                'metakey' => 'required|string',
                'metadescription' => 'required|string',
            ]);
    
            $indexcontent = IndexContent::find($request->id);
            if (!empty($indexcontent)) {
                $indexcontent->update([
                    'heading' => $request->heading,
                    'subheading' => $request->subheading,
                    'watchlink' => $request->watchlink,
                    'attribute' => $request->attribute,
                    'description' => $request->description,
                    'metatitle' => $request->metatitle,
                    'metakey' => $request->metakey,
                    'metadescription' => $request->metadescription,
                    'content' => !empty($filePath) ? $filePath : $indexcontent->content, // Update content only if a new one is uploaded
                ]);
                Session::flash('success', 'Data updated successfully!');
            } else {
                Session::flash('error', 'Content with ID ' . $request->id . ' not found.');
            }
        } else {
            // Validate the incoming request data
            $request->validate([
                'heading' => 'required',
                'subheading' => 'required',
                'watchlink' => 'required|url',
                'attribute' => 'required|string',
                'content' => 'required|mimes:jpeg,png,jpg,webp,mp4,avi,mov,wmv|max:20480', // Adjust the max size as needed
                'description' => 'required|string',
                'metatitle' => 'required|string',
                'metakey' => 'required|string',
                'metadescription' => 'required|string',
            ]);
    
            // Create a new indexcontent instance
            $indexcontent = new IndexContent();
            $indexcontent->heading = $request->heading;
            $indexcontent->subheading = $request->subheading;
            $indexcontent->watchlink = $request->watchlink;
            $indexcontent->attribute = $request->attribute;
            $indexcontent->content = $filePath; // Store only the relative path
            $indexcontent->description = $request->description;
            $indexcontent->metatitle = $request->metatitle;
            $indexcontent->metakey = $request->metakey;
            $indexcontent->metadescription = $request->metadescription;
            $indexcontent->position_by = $position_by;
            $indexcontent->save();
            Session::flash('success', 'Data added successfully!');
        }
    
        // Redirect back with success or error message
        return redirect()->route('admin.indexcontent');
    }
    


    // public function save(Request $request)
    // {
    //     // dd($request->all());
    //     $total = IndexContent::count();
    //     $position_by = $total + 1;
    //     //    dd($request->all());
    //     // Handle file upload
    //     if ($request->hasFile('content')) {
    //         $imagePath = $request->file('content')->store('website', 'public');

    //         // Delete the old content if updating an existing record
    //         if (!empty($request->id)) {
    //            $indexcontent = IndexContent::find($request->id);
    //             if (!empty($indexcontent->content)) {
    //                 Storage::disk('public')->delete($indexcontent->content);
    //             }
    //         }
    //     }

    //     // Check if it's an update operation
    //     if (!empty($request->id)) {
    //         // Validate the incoming request data
    //         $request->validate([
    //             'heading' => 'required',
    //             'subheading' => 'required',
    //             'watchlink' => 'required|url',
    //             'attribute' => 'required|string',
    //             'description' => 'required|string',
    //             'metatitle' => 'required|string',
    //             'metakey' => 'required|string',
    //             'metadescription' => 'required|string',
    //         ]);
    //        $indexcontent = IndexContent::find($request->id);
    //         if (!empty($indexcontent)) {
    //             // dd($request->all());
    //            $indexcontent->update([
    //                 'heading' => $request->heading,
    //                 'subheading' => $request->subheading,
    //                 'watchlink' => $request->watchlink,
    //                 'attribute' => $request->attribute,
    //                 'description' => $request->description,
    //                 'metatitle' => $request->metatitle,
    //                 'metakey' => $request->metakey,
    //                 'metadescription' => $request->metadescription,
    //                 'content' => isset($imagePath) ? $imagePath :$indexcontent->content, // Update content only if a new one is uploaded
    //             ]);
    //             Session::flash('success', 'Data updated successfully!');
    //         } else {
    //             Session::flash('error', 'Shorts with ID ' . $request->id . ' not found.');
    //         }
    //     } else {
    //         $request->validate([
    //             'heading' => 'required',
    //             'subheading' => 'required',
    //             'watchlink' => 'required|url',
    //             'attribute' => 'required',
    //             'content' => 'required|image|mimes:jpeg,png,jpg,webp,mp4.gif|max:2048', // Assuming image uploads
    //             'description' => 'required|string',
    //             'metatitle' => 'required|string',
    //             'metakey' => 'required|string',
    //             'metadescription' => 'required|string',
    //         ]);
    //         // Create a new indexcontent instance
    //        $indexcontent = new IndexContent();
    //        $indexcontent->heading = $request->heading;
    //        $indexcontent->subheading = $request->subheading;
    //        $indexcontent->watchlink = $request->watchlink;
    //        $indexcontent->attribute = $request->attribute;
    //        $indexcontent->content = $imagePath; // Assuming you have a 'content' field in your 'indexcontent' table
    //        $indexcontent->description = $request->description;
    //        $indexcontent->metatitle = $request->metatitle;
    //        $indexcontent->metakey = $request->metakey;
    //        $indexcontent->metadescription = $request->metadescription;
    //        $indexcontent->position_by = $position_by;
    //        $indexcontent->save();
    //         Session::flash('success', 'Data added successfully!');
    //     }

    //     // Redirect back with success or error message
    //     return redirect()->route('admin.indexcontent');
    // }
    // public function save(Request $request)
    // {
    //     $total = IndexContent::count();
    //     $position_by = $total + 1;
    
    //     // Initialize $imagePath
    //     $imagePath = '';
    
    //     // Handle file upload
    //     if ($request->hasFile('content')) {
    //         // Generate a unique name for the file
    //         $fileName = time() . '.' . $request->content->extension();
    
    //         // Upload the file to the 'website' folder in the S3 bucket
    //         $file = $request->file('content');
    //         $path = $file->store('website', 's3');
    
    //         // Get the URL of the uploaded file
    //         $imagePath = Storage::disk('s3')->url($path);
    
    //         // Delete the old content if updating an existing record
    //         if (!empty($request->id)) {
    //             $indexcontent = IndexContent::find($request->id);


    //             // if (!empty($indexcontent) && !empty($indexcontent->content)) {
    //             //     // Extract the S3 path from the URL and delete the file
    //             //     $oldFilePath = str_replace(Storage::disk('s3')->url(''), '', $indexcontent->content);
    //             //     if (!empty($oldFilePath)) {
    //             //         Storage::disk('s3')->delete($oldFilePath);
    //             //     }
    //             // }


    //         }
    //     }
    
    //     // Check if it's an update operation
    //     if (!empty($request->id)) {
    //         // Validate the incoming request data
    //         $request->validate([
    //             'heading' => 'required',
    //             'subheading' => 'required',
    //             'watchlink' => 'required|url',
    //             'attribute' => 'required|string',
    //             'description' => 'required|string',
    //             'metatitle' => 'required|string',
    //             'metakey' => 'required|string',
    //             'metadescription' => 'required|string',
    //         ]);
    
    //         $indexcontent = IndexContent::find($request->id);
    //         if (!empty($indexcontent)) {
    //             $indexcontent->update([
    //                 'heading' => $request->heading,
    //                 'subheading' => $request->subheading,
    //                 'watchlink' => $request->watchlink,
    //                 'attribute' => $request->attribute,
    //                 'description' => $request->description,
    //                 'metatitle' => $request->metatitle,
    //                 'metakey' => $request->metakey,
    //                 'metadescription' => $request->metadescription,
    //                 'content' => !empty($imagePath) ? $imagePath : $indexcontent->content, // Update content only if a new one is uploaded
    //             ]);
    //             Session::flash('success', 'Data updated successfully!');
    //         } else {
    //             Session::flash('error', 'Content with ID ' . $request->id . ' not found.');
    //         }
    //     } else {
    //         // Validate the incoming request data
    //         $request->validate([
    //             'heading' => 'required',
    //             'subheading' => 'required',
    //             'watchlink' => 'required|url',
    //             'attribute' => 'required|string',
    //             'content' => 'required|mimes:jpeg,png,jpg,webp,mp4,avi,mov,wmv|max:20480', // Adjust the max size as needed
    //             'description' => 'required|string',
    //             'metatitle' => 'required|string',
    //             'metakey' => 'required|string',
    //             'metadescription' => 'required|string',
    //         ]);
    
    //         // Create a new indexcontent instance
    //         $indexcontent = new IndexContent();
    //         $indexcontent->heading = $request->heading;
    //         $indexcontent->subheading = $request->subheading;
    //         $indexcontent->watchlink = $request->watchlink;
    //         $indexcontent->attribute = $request->attribute;
    //         $indexcontent->content = $imagePath; // Assuming you have a 'content' field in your 'indexcontent' table
    //         $indexcontent->description = $request->description;
    //         $indexcontent->metatitle = $request->metatitle;
    //         $indexcontent->metakey = $request->metakey;
    //         $indexcontent->metadescription = $request->metadescription;
    //         $indexcontent->position_by = $position_by;
    //         $indexcontent->save();
    //         Session::flash('success', 'Data added successfully!');
    //     }
    
    //     // Redirect back with success or error message
    //     return redirect()->route('admin.indexcontent');
    // }
    

    private function getEmbeddedUrl($url)
    {
        $shortUrlRegex = '/youtu.be\/([a-zA-Z0-9_-]+)\??/i';
        $longUrlRegex = '/youtube.com\/((?:embed)|(?:watch))((?:\?v\=)|(?:\/))([a-zA-Z0-9_-]+)/i';

        if (preg_match($longUrlRegex, $url, $matches) || preg_match($shortUrlRegex, $url, $matches)) {
            $youtube_id = $matches[count($matches) - 1];
            return 'https://www.youtube.com/embed/' . $youtube_id;
        }

        // Return the original URL if no match is found
        return $url;
    }
}
