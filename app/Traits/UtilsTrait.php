<?php

namespace App\Traits;

trait UtilsTrait
{

    public function groupStudents($num_groups, $students)
    {
        // Create an empty list called "groups."
        $groups = array();
        for ($i = 0; $i < $num_groups; $i++) {
            $groups[$i] = array();
        }

        // Fetch the list of students from the Laravel database.
        $num_students = count($students);

        // Create a variable "students_per_group" equal to the number of students divided by the number of groups.
        $students_per_group = floor($num_students / $num_groups);

        // Create a variable "remainder" equal to the number of students modulo the number of groups.
        $remainder = $num_students % $num_groups;

        // Iterate through the list of students, adding them to the groups list
        $student_index = 0;
        for ($group_index = 0; $group_index < $num_groups; $group_index++) {
            $students_to_add = ($group_index < $remainder) ? $students_per_group + 1 : $students_per_group;
            for ($i = 0; $i < $students_to_add; $i++) {
                $groups[$group_index][] = $students[$student_index];
                $student_index++;
            }
        }

        // Return the "groups" list as the result.
        return $groups;
    }

    public function imagePath($folderName)
    {
        $path = base_path('assets/img/' . $folderName);
        if (env('APP_ENV') == 'local') {
            $path = public_path('assets/img/' . $folderName);
        }
        return $path;
    }

    public function pdfPath($folderName)
    {
        $path = base_path('assets/pdf/' . $folderName);
        if (env('APP_ENV') == 'local') {
            $path = public_path('assets/pdf/' . $folderName);
        }
        return $path;
    }


    public function remarks($score)
    {
        $score = (int)$score;
        if ($score === 0 || $score === 0.00) {
            $remark = 'ic';
        } elseif ($score >= 79.5) {
            $remark = 'honour';
        } elseif ($score >= 49.5) {
            $remark = 'pass';
        } else {
            $remark = 'fail';
        }
        return $remark;
    }
}
