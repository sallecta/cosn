/**
 # sudoku.js
 #
 # javascript interface for sudoku app 
 #  
 # @author Alexander Gribkov, Joachim Schmidt - sallecta@yahoo.com
 # @copyright Copyright (C) 2014 Alexander Gribkov, Joachim Schmidt. All rights reserved.
 # @license	 http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 #
 # change activity:
 */
var solution; 
var number_color; 

function printpuzzle()
{
   var divToPrint=document.getElementById("sudoku_grid");
   newWin= window.open("");
   newWin.document.write(divToPrint.outerHTML);
   newWin.print();
   newWin.close();
}	

function getPuzzle (ajaxurl, parms)
{
		var content = "";
	
		var lang = jQuery("#language").text();
		ajaxurl = ajaxurl + "&lang=" + lang;
		
		if (parms == 'null')
		{	
		 var level = 0;	
		 
		 for (var i=0; i<3; ++i) {	
		  if (jQuery("#level"+i).prop("checked") == true)
		 	  level = jQuery("#level"+i).val();
		  }		  
		  ajaxurl = ajaxurl + "&sudokuid=null&level=" + level;
		}
		else
		 ajaxurl = ajaxurl + parms;	

		jQuery("#button1").prop('disabled', true);
		jQuery("#button2").prop('disabled', true);
		jQuery("#button3").prop('disabled', true);
		jQuery("#messages").attr("style", "visibility: hidden"); 
		
		
	    jQuery.ajax({
		url : ajaxurl,
		type : "GET",
		dataType : "json",
		error: function (request, error, error_reason) 
		  { 
			jQuery("#button1").prop('disabled', false);
			jQuery("#button2").prop('disabled', false);
			jQuery("#button3").prop('disabled', false);
			jQuery("#messages").html("<span style='color: red'>Ajax Error - Reason: "+ request.status +' ' +error_reason +"</span>");
			jQuery("#messages").attr("style", "visibility: visible"); 
		  }, 
		success: onSuccess,
		async : true
	});
}

function onSuccess(response)
{	  
	jQuery("#button1").prop('disabled', false);
	jQuery("#button2").prop('disabled', false);
	jQuery("#button3").prop('disabled', false);
		
	var solver = sudoku_solver();
	solution = "";
	var solarr = solver(response.puzzle, 2);
	for (var i = 0; i < solarr.length; ++i) {
		solution += solarr[i].join('') + '\n';
	}
	
	showSudoku(response.puzzle);

	var sudokuid = response.puzzle_id;	
	var lang = jQuery("#language").text();
	if (lang == "de")
	 sudokuid = sudokuid.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
	else
	 sudokuid = sudokuid.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");	
	jQuery("#sudokuid").html("<h2>Sudoku - " + sudokuid + "</h2>");

}

function checkValues()
{
	if (jQuery("#help").prop("checked") == true || jQuery("#check").prop("checked") == true)
	{
		if (jQuery("#help").prop("checked") == true)
		{
		  showCandidates();
		   for (var i=0; i<81; ++i) {
		     if (Query("#i"+i).prop('readonly') == false)		 	 
		       checkValue (i, false, false);	
		   }
		}
		else
		  for (var i=0; i<81; ++i)
		  {	
	        jQuery("#candidates"+i).attr("style", "visibility: hidden"); 
	        checkValue (i, false, false);
	      }    
	}
	else
	  for (var i=0; i<81; ++i)
	  {	
		 jQuery("#candidates"+i).attr("style", "visibility: hidden");  
      }   
	   	
	return;	
}

function checkValue (i, msg, checkCandidates)
{ 
	
	 var val = jQuery("#i"+i).val();
	 
	 if (jQuery("#i"+i).prop('readonly') == true)
	  return; 
	 
	 if ( isNaN(val) || val === '0')
	  {
		 jQuery("#i"+i).val("");
		 return;
	  }	 
	 
	 var val_solution = solution.substr(i, 1);	
	 
	 if (val_solution != val && jQuery("#i"+i).val() != "")
	  {
	    if (jQuery("#help").prop("checked") == true || jQuery("#check").prop("checked") == true)
	     jQuery("#i"+i).attr("style", "color: red");
	  }	 
	 else
	  {	 
	   jQuery("#i"+i).attr("style", "color: green");	 
	   if (jQuery("#help").prop("checked") == true && checkCandidates == true)
		  showCandidates();
	   checkSolved(msg);
	  }
	 return;
}

function checkSolved(msg)
{
      var solved = true;
	  
	  for (var i=0; i<81; ++i) {
	   if (jQuery("#i"+i).val() != solution.substr(i, 1))
         solved = false; 
	   if (jQuery("#i"+i).prop('readonly') == true && number_color == null)
		 number_color = jQuery("#i"+i).css('color');  
	  }
	  
	  if (solved == true && msg == true)
	  {	  
		jQuery("#button2").attr("style", "visibility: hidden"); 	  
		jQuery("#messages").html(jQuery('#msgbox_content').html());
		jQuery("#messages").css('visibility','visible').hide().fadeIn();
	  } 
	  
	  if (solved == false)
		{  
		 jQuery("#button2").attr("style", "visibility: visible");
	     jQuery("#messages").attr("style", "visibility: hidden"); 
		}
	     
	  	  
	  return;
}

function showSudoku(puzzle)
{
  
  for (var i=0; i<81; ++i)
  { 
    if (puzzle.substr(i, 1) != '.')
     {	
    	jQuery("#i"+i).val(puzzle.substr(i, 1));
    	jQuery("#i"+i).attr('readonly', true);
    	jQuery("#i"+i).removeClass('sudoku-nonumber');
    	jQuery("#i"+i).addClass('sudoku-number');
    	if (number_color)
    	  jQuery("#i"+i).css('color', number_color);
     }	
    else
    {	
      	jQuery("#i"+i).val('');
      	jQuery("#i"+i).attr('readonly', false);
      	jQuery("#i"+i).removeClass('sudoku-number');
    	jQuery("#i"+i).addClass('sudoku-nonumber');
    }
	if (jQuery("#help").prop("checked") == true) 
		showCandidates();	
	else		
	 jQuery("#candidates"+i).attr("style", "visibility: hidden");
  }
  jQuery("#button2").attr("style", "visibility: visible");
}

function showCandidates()
{
	var grid = new Array(9);
	for (var i = 0; i < 10; i++) 
	   grid[i] = new Array(9);
	var candidates = [];
		
	 var v = 0;
     for (var i=0; i<9; i++)
     {	 
	   for (var j=0; j<9; j++)	
		{ 
		 grid[i][j] = jQuery("#i"+v).val();
		 if (jQuery("#i"+v).val() != solution.substr(v, 1)) 
		   grid[i][j] = ""; 
	     v++;
		} 
     }  
     
     var row_candidates = new Array(9);
     var col_candidates = new Array(9);
   	 var box_candidates = new Array(9);   	 
     for (var i=0; i<9; i++)
      {
         row_candidates[i] = [0,1,2,3,4,5,6,7,8,9];
      	 col_candidates[i] = [0,1,2,3,4,5,6,7,8,9];
    	 box_candidates[i] = [0,1,2,3,4,5,6,7,8,9];    	     
    	 row_candidates[i] = checkRow(i, grid, row_candidates[i] );
     	 col_candidates[i] = checkColumn (i, grid, col_candidates[i]);
    	 box_candidates[i] = checkBox(i, grid, box_candidates[i]); 
      }    

     k=0;
     for (var i=0; i<9; i++)
     {	 
	   for (var j=0; j<9; j++)	
		{
		   
		 candidates[k] = ""; 
		 var br = false;
         for (var v=1; v<10; v++)
         {	 
             var box = getBoxNr(i, j);       	
           	 if ( row_candidates[i][v] == col_candidates[j][v] && row_candidates[i][v] == box_candidates[box][v])
             {
             	if (row_candidates[i][v] != 0 && col_candidates[j][v] != 0 && box_candidates[box][v] != 0)
            	 if (candidates[k].length > 4 && br == false )
            	  {	 
            	    candidates[k] += "<br />" +row_candidates[i][v];
            	    br = true;
            	  }
            	 else
            		candidates[k] += row_candidates[i][v];	 
             }        	                  
          }
                
         if (jQuery("#i"+k).val() == solution.substr(k, 1))
           jQuery("#candidates"+k).attr("style", "visibility: hidden");
         else   
         {	        
           jQuery("#candidates"+k).html(candidates[k]);
           jQuery("#candidates"+k).attr("style", "visibility: visible");
          } 
         
         k++;
	   } 
     } 
}

function getBoxNr (i, j)
{
        var box = 0;
        
 		if (i < 3 && j < 3) box = 0;
		if (i < 3 && j < 6 && j > 2) box = 1; 
		if (i < 3 && j > 5) box = 2;
   		if (i < 6 && i > 2 && j < 3) box = 3;
		if (i < 6 && i > 2 && j < 6 && j > 2) box = 4; 
		if (i < 6 && i > 2 && j > 5) box = 5;
   		if (i > 5 && j < 3) box = 6;
		if (i > 5 && j < 6 && j > 2) box = 7; 
		if (i > 5 && j > 5) box = 8; 
		
		return box;
}

function checkRow (j, grid, numbers) {

	for (var i=0; i<9; i++)
	 {
		if (grid[j][i] != "" )
		 numbers[ grid[j][i] ] = 0;  
	 }
	return numbers;
}

function checkColumn (j, grid, numbers) {
	
	for (var i=0; i<9; i++)
	 {
		if (grid[i][j] != "")
			numbers[ grid[i][j] ] = 0;  
	 }
	return numbers;
}

function checkBox (box, grid, numbers) {
	
   if (box == 0) { bi=0, bj=0; li=3; lj=3; }
   if (box == 1) { bi=0, bj=3; li=3; lj=6; }
   if (box == 2) { bi=0, bj=6; li=3; lj=9; }
   if (box == 3) { bi=3, bj=0; li=6; lj=3; }
   if (box == 4) { bi=3; bj=3; li=6; lj=6; }
   if (box == 5) { bi=3, bj=6; li=6; lj=9; }
   if (box == 6) { bi=6, bj=0; li=9; lj=3; }
   if (box == 7) { bi=6; bj=3; li=9; lj=6; }
   if (box == 8) { bi=6, bj=6; li=9; lj=9; }
      	   
	for (var i=bi; i<li; i++)
	 {
		for (var j=bj; j<lj; j++)
		{	
		 if (grid[i][j] != "")
			numbers[ grid[i][j] ] = 0;
		}
	 }
	return numbers;
}

function showSolution() 
{
	for (var i=0; i<81; ++i) {
	   if (jQuery("#i"+i).prop('readonly') == false)
	   {	  
		 jQuery("#i"+i).val(solution.substr(i, 1));
		 jQuery("#i"+i).css('color','green');
		 jQuery("#candidates"+i).attr("style", "visibility: hidden");
	   }
	   else
	   	{   
		 if (number_color == null)  
		  number_color = jQuery("#i"+i).css('color');
	   	} 
	}

	jQuery("#button2").attr("style", "visibility: hidden");
}

/*
 * The MIT License
 * 
 * Copyright (c) 2011 by Attractive Chaos <attractor@live.co.uk>
 * 
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 * 
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 * 
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 */

/*
 * For Sudoku, there are 9x9x9=729 possible choices (9 numbers to choose for
 * each cell in a 9x9 grid), and 4x9x9=324 constraints with each constraint
 * representing a set of choices that are mutually conflictive with each other.
 * The 324 constraints are classified into 4 categories:
 * 
 * 1. row-column where each cell contains only one number 2. box-number where
 * each number appears only once in one 3x3 box 3. row-number where each number
 * appears only once in one row 4. col-number where each number appears only
 * once in one column
 * 
 * Each category consists of 81 constraints. We number these constraints from 0
 * to 323. In this program, for example, constraint 0 requires that the (0,0)
 * cell contains only one number; constraint 81 requires that number 1 appears
 * only once in the upper-left 3x3 box; constraint 162 requires that number 1
 * appears only once in row 1; constraint 243 requires that number 1 appears
 * only once in column 1.
 * 
 * Noting that a constraint is a subset of choices, we may represent a
 * constraint with a binary vector of 729 elements. Thus we have a 729x324
 * binary matrix M with M(r,c)=1 indicating the constraint c involves choice r.
 * Solving a Sudoku is reduced to finding a subset of choices such that no
 * choices are present in the same constaint. This is equivalent to finding the
 * minimal subset of choices intersecting all constraints, a minimum hitting set
 * problem or a eqivalence of the exact cover problem.
 * 
 * The 729x324 binary matrix is a sparse matrix, with each row containing 4
 * non-zero elements and each column 9 non-zero elements. In practical
 * implementation, we store the coordinate of non-zero elements instead of the
 * binary matrix itself. We use a binary row vector to indicate the constraints
 * that have not been used and use a column vector to keep the number of times a
 * choice has been forbidden. When we set a choice, we will use up 4 constraints
 * and forbid other choices in the 4 constraints. When we make wrong choices, we
 * will find an unused constraint with all choices forbidden, in which case, we
 * have to backtrack to make new choices. Once we understand what the 729x324
 * matrix represents, the backtracking algorithm itself is easy.
 * 
 * A major difference between the algorithm implemented here and Guenter
 * Stertenbrink's suexco.c lies in how to count the number of the available
 * choices for each constraint. Suexco.c computes the count with a loop, while
 * the algorithm here keeps the count in an array. The latter is a little more
 * complex to implement as we have to keep the counts synchronized all the time,
 * but it is 50-100% faster, depending on the input.
 */

function sudoku_solver() {
	var C = [], R = [];
	{ // generate the sparse representation of the binary matrix
		var i, j, r, c, c2;
		for (i = r = 0; i < 9; ++i) // generate c[729][4]
			for (j = 0; j < 9; ++j)
				for (k = 0; k < 9; ++k)
					// the 4 numbers correspond to row-col, box-num, row-num and
					// col-num constraints
					C[r++] = [ 9 * i + j, (Math.floor(i/3)*3 + Math.floor(j/3)) * 9 + k + 81, 9 * i + k + 162, 9 * j + k + 243 ];
		for (c = 0; c < 324; ++c) R[c] = [];
		for (r = 0; r < 729; ++r) // generate r[][] from c[][]
			for (c2 = 0; c2 < 4; ++c2)
				R[C[r][c2]].push(r);
	}

	// update the state vectors when we pick up choice r; v=1 for setting
	// choice; v=-1 for reverting
	function sd_update(sr, sc, r, v) {
		var min = 10, min_c = 0;
		for (var c2 = 0; c2 < 4; ++c2) sc[C[r][c2]] += v<<7;
		for (var c2 = 0; c2 < 4; ++c2) { // update # available choices
			var r2, rr, cc2, c = C[r][c2];
			if (v > 0) { // move forward
				for (r2 = 0; r2 < 9; ++r2) {
					if (sr[rr = R[c][r2]]++ != 0) continue; // update the row
															// status
					for (cc2 = 0; cc2 < 4; ++cc2) {
						var cc = C[rr][cc2];
						if (--sc[cc] < min) // update # allowed choices
							min = sc[cc], min_c = cc; // register the minimum
														// number
					}
				}
			} else { // revert
				for (r2 = 0; r2 < 9; ++r2) {
					if (--sr[rr = R[c][r2]] != 0) continue; // update the row
															// status
					var p = C[rr];
					++sc[p[0]]; ++sc[p[1]]; ++sc[p[2]]; ++sc[p[3]]; // update
																	// the count
																	// array
				}
			}
		}
		return min<<16 | min_c;  // return the col that has been modified and
									// with the minimal available choices
	}

	// solve a Sudoku; _s is the standard dot/number representation; max_ret
	// sets the maximum number of returned solutions
	return function(_s, max_ret) {
		var r, c, r2, min, cand, dir, hints = 0; // dir=1: forward; dir=-1:
													// backtrack
		// sr[r]: # times the row is forbidden by others; cr[i]: row chosen at
		// step i
		// sc[c]: bit 1-7 - # allowed choices; bit 8: the constraint has been
		// used or not
		// cc[i]: col chosen at step i
		var sr = [], sc = [], cr = [], cc = [], out = [], ret = []; 
		if (max_ret == null) max_ret = 2;
		for (r = 0; r < 729; ++r) sr[r] = 0; // no row is forbidden
		for (c = 0; c < 324; ++c) sc[c] = 9; // 9 allowed choices; no
												// constraint has been used
		for (var i = 0; i < 81; ++i) {
			var a = _s.charAt(i) >= '1' && _s.charAt(i) <= '9'? _s.charCodeAt(i) - 49 : -1; // number
																							// from
																							// -1
																							// to 8
			if (a >= 0) sd_update(sr, sc, i * 9 + a, 1); // set the choice
			if (a >= 0) ++hints; // count the number of hints
			cr[i] = cc[i] = -1, out[i] = a + 1;
		}
		for (var i = 0, dir = 1, cand = 10<<16|0;;) {
			while (i >= 0 && i < 81 - hints) { // at most 81-hints steps
				if (dir == 1) {
					min = cand>>16, cc[i] = cand&0xffff;
					if (min > 1) {
						for (c = 0; c < 324; ++c) {
							if (sc[c] < min) {
								min = sc[c], cc[i] = c; // choose the top
														// constraint
								if (min <= 1) break; // this is for
														// acceleration; slower
														// without this line
							}
						}
					}
					if (min == 0 || min == 10) cr[i--] = dir = -1; // backtrack
				}
				c = cc[i];
				if (dir == -1 && cr[i] >= 0) sd_update(sr, sc, R[c][cr[i]], -1); // revert
																					// the
																					// choice
				for (r2 = cr[i] + 1; r2 < 9; ++r2) // search for the choice to
													// make
					if (sr[R[c][r2]] == 0) break; // found if the state equals
													// 0
				if (r2 < 9) {
					cand = sd_update(sr, sc, R[c][r2], 1); // set the choice
					cr[i++] = r2; dir = 1; // moving forward
				} else cr[i--] = dir = -1; // backtrack
			}
			if (i < 0) break;
			var y = [];
			for (var j = 0; j < 81; ++j) y[j] = out[j];
			for (var j = 0; j < i; ++j) r = R[cc[j]][cr[j]], y[Math.floor(r/9)] = r%9 + 1; // the
																							// solution
																							// array
																							// (81
																							// numbers)
			ret.push(y);
			if (ret.length >= max_ret) return ret;
			--i; dir = -1; // backtrack
		}
		return ret;
	}
}

/*
 * ===== USAGE =====
 * 
 * var solver = sudoku_solver() var solstr, solarr =
 * solver('..............3.85..1.2.......5.7.....4...1...9.......5......73..2.1........4...9',
 * 2) for (var i = 0; i < solarr.length; ++i) { solstr += solarr[i].join('') +
 * '\n' } alert(solstr)
 * 
 */

