<?php
/**
 * Created by PhpStorm.
 * User: shayao
 * Date: 2018/4/6
 * Time: 15:33
 */

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class SettingController extends Controller
{
    public function __construct(Request $request)
    {
        if ($request->has('s')) {
            if ($request->input('s') != 'redcat') {
                abort(404);
            }
        } else {
            abort(404);
        }
    }

    public function create(Request $request)
    {
        $model = Setting::create($request->all());
        self::SetCache();
        return $model;
    }

    public function update(Request $request, $id)
    {
        $model = Setting::find($id);
        $model->key = $request->input('key', $model->key);
        $model->value = $request->input('value', $model->value);
        $model->des = $request->input('des', $model->des);
        $model->save();
        self::SetCache();
        return $model;
    }

    public function delete($id)
    {
        $model = Setting::find($id);
        $model->delete();
        self::SetCache();
        return '删除成功';
    }

    public function index()
    {
        $model = Setting::all();
        return $model;
    }

    private function SetCache()
    {
        Cache::forever('setting', IndexBy(Setting::all(), 'key'));
    }


}