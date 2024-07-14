CREATE OR REPLACE  FUNCTION find_in_set(str text, strlist text) RETURNS int
AS
DECLARE b1 VARCHAR;
begin
 b1:=array_position(string_to_array($2, ','),$1);
RETURN b1;
end;

