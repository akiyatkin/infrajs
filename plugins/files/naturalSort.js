
// http://habrahabr.ru/blogs/algorithm/127943/
//
function naturalSort(array, extractor) {
  var splitters = array.map(makeSplitter);
  var sorted = splitters.sort(compareSplitters);
  return sorted.map(function(splitter) {
    return splitter.item
  });
  function makeSplitter(item) {
    return new Splitter(item)
  }
  function Splitter(item) {
    var index = 0;
    var from = 0;
    var parts = [];
    var completed = false;
    this.item = item;
    var key = typeof extractor === "function" ? extractor(item) : item;
    this.key = key;
    this.count = function() {
      return parts.length
    };
    this.part = function(i) {
      while(parts.length <= i && !completed) {
        next()
      }
      return i < parts.length ? parts[i] : null
    };
    function next() {
      if(index < key.length) {
        while(++index) {
          var currentIsDigit = isDigit(key.charAt(index - 1));
          var nextChar = key.charAt(index);
          var currentIsLast = index === key.length;
          var isBorder = currentIsLast || xor(currentIsDigit, isDigit(nextChar));
          if(isBorder) {
            var partStr = key.slice(from, index);
            parts.push(new Part(partStr, currentIsDigit));
            from = index;
            break
          }
        }
      }else {
        completed = true
      }
    }
    function Part(text, isNumber) {
      this.isNumber = isNumber;
      this.value = isNumber ? Number(text) : text
    }
  }
  function compareSplitters(sp1, sp2) {
    var i = 0;
    do {
      var first = sp1.part(i);
      var second = sp2.part(i);
      if(null !== first && null !== second) {
        if(xor(first.isNumber, second.isNumber)) {
          return first.isNumber ? -1 : 1
        }else {
          var comp = compare(first.value, second.value);
          if(comp != 0) {
            return comp
          }
        }
      }else {
        return compare(sp1.count(), sp2.count())
      }
    }while(++i);
    function compare(a, b) {
      return a < b ? -1 : a > b ? 1 : 0
    }
  }
  function xor(a, b) {
    return a ? !b : b
  }
  function isDigit(chr) {
    var code = charCode(chr);
    return code >= charCode("0") && code <= charCode("9");
    function charCode(c) {
      return c.charCodeAt(0)
    }
  }
}
;

module.exports = naturalSort;
